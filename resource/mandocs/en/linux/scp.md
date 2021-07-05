# scp

## copy from remote

### copy file

```bash
scp root@192.168.1.100:/data/test.txt /home/myfile/
```

with port:

```bash
scp -P 233 root@192.168.1.100:/data/test.txt /home/myfile/
```

multi files:

```bash
scp root@192.168.1.100:/data/\{test1.txt,test2.cpp,test3.bin,test.*\} /home/myfile/
```

### copy directory

```bash
scp -r root@192.168.1.100:/data/ /home/myfile/
```

## send local file to remote

### send file

```bash
scp /home/myfile/test.txt root@192.168.1.100:/data/
// multi files
scp /home/myfile/test1.txt test2.cpp test3.bin test.* root@192.168.1.100:/data/
```

### send dir

```bash
scp -r /home/myfile/ root@192.168.1.100:/data/
```