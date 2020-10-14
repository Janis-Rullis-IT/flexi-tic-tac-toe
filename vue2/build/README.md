# `build`

Code required for building the project.

## NGINX

- NGINX web server parses the request - throws and error if it is forbidden (`.conf`, `.log`) else send the request to `public/index.html`.

## webpack

Compiles Vue.js [`src`](../src/README.md) and `node_modules`, also provides lazy loading (loads code only when needed).