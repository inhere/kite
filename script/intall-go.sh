#!/usr/bin/env bash
# install go on linux

GO_VER=1.17.6
GO_TAR=go$GO_VER.linux-amd64.tar.gz
echo "Will install the $GO_TAR"

cd /tmp || exit 2
# mac https://studygolang.com/dl/golang/go1.17.6.darwin-amd64.pkg
# linux https://studygolang.com/dl/golang/go1.17.6.linux-amd64.tar.gz
wget https://studygolang.com/dl/golang/$GO_TAR

# decompress
tar -C /usr/local -xzf $GO_TAR

cat <<EOF
The go $GO_VER download ok and decompress to /usr/local/go.
please config something for enable it.

# config:
# .bashrc OR /etc/profile 最后一行添加
export PATH=\$PATH:/usr/local/go/bin

# check install
go version
# go env -w GOPROXY=https://goproxy.io,direct
go env -w GOPROXY=https://goproxy.cn,direct
go env
EOF
