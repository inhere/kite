# curl

examples:

```bash
curl \
  -X POST \
  -H "Accept: application/vnd.github.v3+json" \
  -H "Content-Type: application/gzip" \
  https://api.github.com/repos/octocat/hello-world/releases/42/assets
```

send a file:

```bash
curl \
  -X POST \
  -H "Accept: application/vnd.github.v3+json" \
  -H "Content-Type: application/zip" \
  -T example.zip \
  https://api.github.com/repos/octocat/hello-world/releases/42/assets
```

