#!/usr/bin/env python
# Compatible with XenServer 8.2 (Python 2.7.5)
import time
import subprocess
import json
import sys
import urllib2
import socket
from datetime import datetime

def run_ipmitool():
    """Run 'ipmitool sensor' and return its output."""
    try:
        proc = subprocess.Popen(["ipmitool", "sensor"], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        out, err = proc.communicate()
        if proc.returncode != 0:
            sys.stderr.write("[ERROR] ipmitool failed: %s\n" % err)
            sys.exit(1)
        return out.strip()
    except OSError:
        sys.stderr.write("[ERROR] ipmitool not found in PATH.\n")
        sys.exit(1)


def parse_ipmitool_output(raw_output):
    """Parse 'ipmitool sensor' into list of dicts."""
    sensors = []
    for line in raw_output.splitlines():
        line = line.strip()
        if not line or "|" not in line:
            continue
        parts = [p.strip() for p in line.split("|")]
        if len(parts) >= 3:
            sensors.append({
               "sensor_name": parts[0],
               "reading": parts[1],
               "units": parts[2],
               "status": parts[3],
               "LNR": parts[4],
               "LCR": parts[5],
               "LNC": parts[6],
               "UNC": parts[7],
               "UCR": parts[8],
               "UNR": parts[9],
            })
    return sensors


def post_to_api(payload):
    """POST data to REST API using urllib2."""
    headers = {"Content-Type": "application/json"}
    if API_TOKEN:
        headers["Authorization"] = "Bearer %s" % API_TOKEN

    data = json.dumps(payload)
    req = urllib2.Request(API_URL, data, headers)
    try:
        resp = urllib2.urlopen(req, timeout=10)
        sys.stdout.write("[INFO] Sent data successfully: %s\n" % resp.getcode())
    except urllib2.HTTPError as e:
        sys.stderr.write("[ERROR] API HTTPError: %s - %s\n" % (e.code, e.reason))
    except urllib2.URLError as e:
        sys.stderr.write("[ERROR] API URLError: %s\n" % e.reason)
    except Exception as e:
        sys.stderr.write("[ERROR] Unexpected error: %s\n" % e)

def main():
    raw = run_ipmitool()
    sensors = parse_ipmitool_output(raw)
    payload = {
        "hostname": socket.gethostname(),
        "timestamp": int(time.time()),
        "sensors": sensors
    }

    # print debug info
    sys.stdout.write(json.dumps(payload, indent=2) + "\n")

    url = sys.argv[1]
    token = sys.argv[2] if len(sys.argv) > 2 else ""

    json_str = json.dumps(payload).replace('"', '\\"')

    command = "curl -X POST "+url+" -s -H \"Content-Type: application/x-www-form-urlencoded\" -d \"payload="+json_str+"\""
    subprocess.call(command, shell=True)

if __name__ == "__main__":
    main()
