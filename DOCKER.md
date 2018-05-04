# [![OpenCFP Banner](docs/img/banner.png)](https://github.com/opencfp/opencfp)

OpenCFP is a PHP-based conference talk submission system.

---
## Using Docker for Development

Now that we have [Docker](https://docker.com) image that we can build, it is easy for us to setup a docker 
development environment, you need to follow the following steps:

*  You need to create a new `docker-compose.yml` file from our `docker-compos.yml.dist` file.

* Modify the `app.environment` section to look like the following:

```
    environment:
      - "CFP_ENV=development"
      - "CFP_DB_HOST=database"
      - "CFP_DB_PASS=root"
```

* Modify the `app.volumes` section to look like the following:

```
    volumes:
      - ./:/var/www
      - ./config/docker.yml.dist:/var/www/config/development.yml
      - ./.docker/script:/var/www/script
```

The most important section is the `app.volumes` section, as it will make sure that we share our code with the docker 
container.

The following, will create a shared volume with the container, so any file you create in your code will automatically 
copied over to the container.

```
 - ./:/var/www
```

Meanwhile, The following will create a copy from our `docker.yml.dist` file and call it `development.yml` file within 
the container, you can remove this line and create your own `.yml` file.

```
- ./config/docker.yml.dist:/var/www/config/development.yml
```

The last part, is the important one, as the scripts was written in `bash` mean while the docker image depends on `alpine`
to make sure it has a small size (about *270MB* in total), so we needs to copy over the docker specific files to the
container, and replace the one on the container.

```
- ./.docker/script:/var/www/script
```

This will not overwrite the original `script` files, but will make our liver easier and will make the scripts 
compatible with `sh`.

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

_PS_: You can access MySQL in the same way we described in the Docker section.
