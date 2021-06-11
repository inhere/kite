# build

usage: `go build [-o output] [-i] [build flags] [packages]`

go build usage examples:

```bash
go build some/path/app.go
go build some/path/app.go -o myapp
```

## build with flags

```bash
# Full build flags used when building binaries
BUILDFLAGS :=  -ldflags \
  " -X $(ROOT_PACKAGE)/pkg/cmd/version.Version=$(VERSION)\
		-X github.com/jenkins-x-plugins/jx-gitops/pkg/cmd/version.Version=$(VERSION)\
		-X $(ROOT_PACKAGE)/pkg/cmd/version.Revision='$(REV)'\
		-X $(ROOT_PACKAGE)/pkg/cmd/version.Branch='$(BRANCH)'\
		-X $(ROOT_PACKAGE)/pkg/cmd/version.BuildDate='$(BUILD_DATE)'\
		-X $(ROOT_PACKAGE)/pkg/cmd/version.GoVersion='$(GO_VERSION)'\
		$(BUILD_TIME_CONFIG_FLAGS)"

go build $(BUILDFLAGS) -o myapp
```