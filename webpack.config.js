const webpack = require('webpack');
const path = require('path');
const glob = require('glob');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const PurifyCSSPlugin = require('purifycss-webpack');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const BuildManifestPlugin = require('./app/assets/js/plugins/BuildManifestPlugin');
const inProduction = (process.env.NODE_ENV === 'production');

module.exports = {
    entry: {
        main: [
            './app/assets/js/main.js',
            './app/assets/styles/main.scss'
        ],
        vendor: [
            'jquery',
            'bootstrap',
            './node_modules/bootstrap-ui/dist/js/bootstrap-ui.js',
            'moment',
            'eonasdan-bootstrap-datetimepicker',
            'bootstrap-datepicker-webpack',
            'live-form-validation'
        ]
    },
    output: {
        path: path.resolve(__dirname, 'www'),
        publicPath: '/srazvs/www/',
        filename: 'js/[name].[chunkhash].js',
    },
    resolve: {
        /*alias: {
            jquery: "jquery/src/jquery"
        }*/
        alias: [{
            // Force all modules to use the same jquery version.
            alias: 'jquery',
            name: path.join(__dirname, 'node_modules/jquery/src/jquery')
        },
        {
            alias: 'datetimepicker',
            name: 'eonasdan-bootstrap-datetimepicker/bootstrap-datetimepicker.js'
        }]
    },
    watchOptions: {
        poll: true
    },
    module: {
        rules: [
            {
                test: /\.s[ac]ss$/,
                use: ExtractTextPlugin.extract({
                    use: [
                        'css-loader',
                        'sass-loader'
                    ],
                    fallback: 'style-loader'
                })
            },
            {
                test: /\.(svg|eot|ttf|woff|woff2)$/,
                loaders: [
                    {
                        loader: 'file-loader',
                        options: {
                            name: 'fonts/[name].[ext]'
                        }
                    }
                ]
            },
            {
                test: /\.(png|jpe?g|gif)$/,
                loaders: [
                    {
                        loader: 'file-loader',
                        options: {
                            name: 'images/[name].[hash].[ext]'
                        }
                    },
                    'img-loader'
                ]
            },
            {
                test: /\.css$/,
                use: [
                    'style-loader',
                    'css-loader'
                ]
            },
            {
                test: /\.js$/,
                exclude: /node_modules/,
                loader: "babel-loader"
            }
        ]
    },
    plugins: [
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery',
            'window.jQuery': 'jquery',
            'window.$': 'jquery'
        }),
        new ExtractTextPlugin('css/[name].[contenthash].css'),
        new webpack.LoaderOptionsPlugin({
            minimize: inProduction
        }),
        /*new PurifyCSSPlugin({
            paths: glob.sync(path.join(__dirname, 'app/templates/!*.latte')),
            minimize: inProduction
        }),*/
        /*new CleanWebpackPlugin(['dist'], {
            root: __dirname,
            verbose: true,
            dry: false
        }),*/
        new BuildManifestPlugin()
    ]
};

if (inProduction) {
    module.exports.plugins.push(
        new webpack.optimize.UglifyJsPlugin()
    );
}
