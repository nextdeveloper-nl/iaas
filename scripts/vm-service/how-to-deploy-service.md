#ubuntu 22
```
sudo nano /etc/systemd/system/plusclouds.service
```

Copy and paste the plusclouds.service inside.

Then;
```
sudo systemctl enable plusclouds.service
sudo systemctl daemon-reload
sudo systemctl start plusclouds.service
```
