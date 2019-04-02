# [![OpenCFP Banner](docs/img/banner.png)](https://github.com/opencfp/opencfp)

OpenCFP is a PHP-based conference talk submission system.

---
## Using Docker for Development

Now that we have [Docker](https://docker.com) image that we can build, it is easy for us to setup a docker 
development environment, you need to follow the following steps:

* You need to create a new `docker-compose.yml` file from our `docker-compose.yml.dist` file.

* Modify the `app.environment` section to look like the following:

```
    environment:
      - "CFP_ENV=development"
      - "CFP_DB_HOST=database"
      - "CFP_DB_PASS=root"
```

## Run Docker

Now all you have to do is to run the following command:

```
$ docker-compose up -d
```

Which will automatically build the images for your if its not already built.

and to stop your containers you can run:

```
$ docker-compose down
```

_PS_: You can access MySQL in the same way we described in the Docker section of README file.
