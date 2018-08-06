FeldsTusServerBundle
====================


This bundle implements the [tus protocol](https://tus.io/) for resumable and asyncronous file uploads.


## Prerequisites

To be done...



## Installation

### Install and config the bundle

To be done...

### Import the routes

On your app's routes configuration, import the bundle's `Controller` folder under your preferred prefix:

```yaml
# config/routes.yaml
felds_tus_server_bundle:
    resource: "@FeldsTusServerBundle/Controller/"
    prefix: tus
    type: annotation
```
