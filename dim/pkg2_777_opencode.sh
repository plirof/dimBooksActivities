cp -n -r /mnt/sdb1/OPENCODE_STUFF/.local/share/* /home/puppy/.local/share/
chmod -R 777 /home/puppy/.local/share/

chmod -R 777 ./$1
chown -R daemon:daemon ./$1
