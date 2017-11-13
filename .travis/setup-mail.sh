# Set up dummy SMTP process
sudo service postfix stop
smtp-sink -d "%d.%H.%M.%S" localhost:2500 1000 &