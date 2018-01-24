const Path = require('path');
const webpack = require('webpack');
// Import the core config
const webpackConfig = require('@silverstripe/webpack-config');
const {
  resolveJS,
  externalJS,
  moduleJS,
  pluginJS,
  moduleCSS,
  pluginCSS,
} = webpackConfig;

const ENV = process.env.NODE_ENV;
const PATHS = {
  // the root path, where your webpack.config.js is located.
  ROOT: Path.resolve(),
  // your node_modules folder name, or full path
  MODULES: 'node_modules',
  // relative path from your css files to your other files, such as images and fonts
  FILES_PATH: '../',
  // the root path to your javascript source files
  SRC: Path.resolve('client/src'),
  DIST: Path.resolve('client/dist')
};

cssRules = moduleCSS(ENV, PATHS);
// Specify the assets rule and remove svg. Currently using index, which is not optimal…
cssRules.rules[2].test = /\.(png|gif|jpe?g)$/;
cssRules.rules.splice(0, 0, {
  test: /\.svg$/,
  exclude: /fonts\/([\w_-]+)\.svg$/,
  loader: 'svg-url-loader',
  options: {
    stripdeclarations: true
  },
});

const config = [
  {
    name: 'js',
    entry: {
      main: `${PATHS.SRC}/boot/index.js`
    },
    output: {
      path: PATHS.DIST,
      filename: 'js/[name].js',
    },
    devtool: (ENV !== 'production') ? 'source-map' : '',
    resolve: Object.assign({}, resolveJS(ENV, PATHS), {extensions: ['.json', '.js', '.jsx']}),
    externals: externalJS(ENV, PATHS),
    module: moduleJS(ENV, PATHS),
    plugins: pluginJS(ENV, PATHS),
  },
  {
    name: 'css',
    entry: {
      main: `${PATHS.SRC}/styles/main.scss`,
      debug: `${PATHS.SRC}/styles/debug.scss`
    },
    output: {
      path: PATHS.DIST,
      filename: 'styles/[name].css',
    },
    devtool: (ENV !== 'production') ? 'source-map' : '',
    module: cssRules,
    plugins: pluginCSS(ENV, PATHS),
  },
];

// Use WEBPACK_CHILD=js or WEBPACK_CHILD=css env var to run a single config
module.exports = (process.env.WEBPACK_CHILD)
  ? config.find((entry) => entry.name === process.env.WEBPACK_CHILD)
  : module.exports = config;
