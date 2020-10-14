# `src`

Vue.js source code - this where the magic lives.

## Structure

`components` - tiny code parts used in [pages](#pages).

### `layouts`

Outer layout used in [pages](#pages).

### `pages`

Consists of [layouts](#layouts) and [components](#components).

### `router`

Tells which [pages](#pages) to load when a specific URL is called.

### `app.js`

Vue.js config used in [App.vue](#App.vue).

### `App.vue`

The initial Vue.js wrapper.

## Request flow

See [Request-flow.md](../Request-flow.md)