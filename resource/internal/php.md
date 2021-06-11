# php

## Packagist

### Searching for packages

```bash
GET https://packagist.org/search.json?q=[query]

{
  "results" : [
    {
      "name": "[vendor]/[package]",
      "description": "[description]",
      "url": "https://packagist.org/packages/[vendor]/[package]",
      "repository": [repository url],
      "downloads": [number of downloads],
      "favers": [number of favers]
    },
    ...
  ],
  "total": [number of results],
  "next": "https://packagist.org/search.json?q=[query]&page=[next page number]"
}
```

Working example: `https://packagist.org/search.json?q=monolog`

### Create a package

> doc https://packagist.org/apidoc#create-package

This endpoint creates a package for a specific repo. Parameters username and apiToken are required. Only POST method is allowed.

```bash
POST https://packagist.org/api/create-package?username=[username]&apiToken=[apiToken] -d '{"repository":{"url":"[url]"}}'

{
  "status": "success"
}
```

Working example:

```bash
curl -X POST 'https://packagist.org/api/create-package?username=zqfan&apiToken=********' \
 -d '{"repository":{"url":"https://github.com/monolog/monolog"}}'
```