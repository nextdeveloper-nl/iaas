local redis = require "resty.redis"
local cjson = require "cjson"

-- -------------------------------------------------------
-- Config
-- -------------------------------------------------------
local REDIS_HOST     = ""
local REDIS_PORT     = 6379
local REDIS_TIMEOUT  = 1000
local REDIS_PASSWORD = ""
local REDIS_DB       = 2
local TOKEN_PREFIX   = "leo:console:"

-- -------------------------------------------------------
-- Helpers
-- -------------------------------------------------------
local function fail(status, message)
    ngx.status = status
    ngx.header.content_type = "application/json"
    ngx.say(cjson.encode({ error = message }))
    ngx.exit(status)
end

local function get_token()
    local ws_protocol = ngx.req.get_headers()["Sec-WebSocket-Protocol"]
    if not ws_protocol or ws_protocol == "" then
        ngx.log(ngx.WARN, "Sec-WebSocket-Protocol header is missing")
        return nil
    end

    ngx.log(ngx.INFO, "Sec-WebSocket-Protocol received: ", ws_protocol)

    for part in ws_protocol:gmatch("[^,%s]+") do
        if part ~= "binary" and part ~= "base64" and part ~= "null" then
            ngx.log(ngx.INFO, "Token extracted from Sec-WebSocket-Protocol: ", part)
            return part
        end
    end

    ngx.log(ngx.WARN, "No valid token found in Sec-WebSocket-Protocol: ", ws_protocol)
    return nil
end

local function connect_redis()
    local red = redis:new()
    red:set_timeout(REDIS_TIMEOUT)

    local ok, err = red:connect(REDIS_HOST, REDIS_PORT)
    if not ok then
        ngx.log(ngx.ERR, "Redis connect failed: ", err)
        return nil, err
    end

    if red:get_reused_times() == 0 then
        local res, err = red:auth(REDIS_PASSWORD)
        if not res then
            ngx.log(ngx.ERR, "Redis auth failed: ", err)
            return nil, err
        end

        local res, err = red:select(REDIS_DB)
        if not res then
            ngx.log(ngx.ERR, "Redis select db failed: ", err)
            return nil, err
        end

        ngx.log(ngx.INFO, "Redis connected to db: ", REDIS_DB)
    end

    return red
end

-- -------------------------------------------------------
-- Main Auth Logic
-- -------------------------------------------------------
local token = get_token()

if not token or token == "" then
    ngx.log(ngx.WARN, "Missing console token")
    fail(ngx.HTTP_BAD_REQUEST, "missing console token")
end

ngx.log(ngx.INFO, "Token to look up: ", token)

local red, err = connect_redis()
if not red then
    ngx.log(ngx.ERR, "Failed to connect Redis: ", tostring(err))
    fail(ngx.HTTP_INTERNAL_SERVER_ERROR, "service unavailable")
end

ngx.log(ngx.INFO, "Redis connected successfully")

local redis_key = TOKEN_PREFIX .. token
ngx.log(ngx.INFO, "Looking up Redis key: ", redis_key)

red:init_pipeline()
red:get(redis_key)
red:del(redis_key)
local results, err = red:commit_pipeline()

if not results then
    ngx.log(ngx.ERR, "Redis pipeline failed: ", err)
    fail(ngx.HTTP_INTERNAL_SERVER_ERROR, "service unavailable")
end

ngx.log(ngx.INFO, "Redis result[1] type: ", type(results[1]))
ngx.log(ngx.INFO, "Redis result[1] value: ", tostring(results[1]))

local session_url = results[1]

if session_url == ngx.null or not session_url then
    ngx.log(ngx.WARN, "Token not found or expired: ", token,
            " key: ", redis_key,
            " db: ", REDIS_DB)
    fail(ngx.HTTP_UNAUTHORIZED, "token expired or invalid")
end

ngx.log(ngx.INFO, "Session URL from Redis: ", session_url)

-- Parse ws://user:password@host/path?query into parts
local user, password, host, path = session_url:match("^wss?://([^:]+):([^@]+)@([^/]+)(/.+)$")

if not host then
    ngx.log(ngx.ERR, "Failed to parse session URL: ", session_url)
    fail(ngx.HTTP_INTERNAL_SERVER_ERROR, "session error")
end

ngx.log(ngx.INFO, "Parsed - host: ", host, " path: ", path, " user: ", user)

-- Whitelist: only allow connections to known XenServer hosts
local allowed_hosts = {
    ["10.1.32.51"] = true,
    ["10.1.32.52"] = true,
}

if not allowed_hosts[host] then
    ngx.log(ngx.ERR, "Blocked attempt to proxy to unlisted host: ", host)
    fail(ngx.HTTP_FORBIDDEN, "forbidden")
end

-- Encode credentials as Basic Auth header
local credentials = ngx.encode_base64(user .. ":" .. password)

-- proxy_pass does NOT support credentials in URL, use Authorization header instead
ngx.var.proxy_target = "http://" .. host .. path
ngx.var.proxy_host   = host
ngx.req.set_header("Authorization", "Basic " .. credentials)
ngx.var.ws_protocol = ws_protocol

ngx.log(ngx.INFO, "Console proxy authorized - target: http://", host, path)
