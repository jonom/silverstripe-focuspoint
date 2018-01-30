# Build Tools

This module uses [webpack](https://webpack.js.org/) to build JS and CSS files. 
This is in line with how the SilverStripe core-modules build their client-assets.

## Writing CSS

We use [SASS](http://sass-lang.com/) to write CSS. The build-tools include an auto-prefixer that will add browser-prefixes if needed.

## Writing JavaScript

JavaScript should be written as ES6.
Plain JavaScript file should use the `.js` extension, while React components use `.jsx`.
The files will be transpiled to a single (browser consumable) JavaScript file using webpack and babel.

## Building JS and CSS

First of all, you need to install the [yarn](https://yarnpkg.com/en/) package manager on your system.
Then do the following:

```bash
cd ./vendor/jonom/focuspoint
yarn
```

This will install all the npm dependencies in `node_modules`

#### Development

During development, you can use the following command to create a development build:

```bash
yarn run dev
```

There's also a "watch" command that will watch your files and build new JS and CSS every time you change a file:

```bash
yarn run watch
```

#### Release

To create a release build, do:

```bash
yarn run build
```

