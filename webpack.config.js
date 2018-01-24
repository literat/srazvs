const webpack = require('webpack');
const path = require('path');
const glob = require('glob-all');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const PurifyCSSPlugin = require('purifycss-webpack');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const BuildManifestPlugin = require('./app/assets/js/plugins/BuildManifestPlugin');
const inProduction = (process.env.NODE_ENV === 'production');

const ExtractTextPluginConfig = (minify) => ({
    fallback: 'style-loader',
    use: [{
        loader: 'css-loader',
        options: {
            minimize: minify,
            sourceMap: true
        }
    }, {
        loader: 'sass-loader',
        options: {
            sourceMap: true
        }
    }]
})

module.exports = ({ minify = false, production = false } = {}) => {
    const extractStylesheet = new ExtractTextPlugin('css/[name].[contenthash].css')
    const plugins = [
        new webpack.ProvidePlugin({
            $:      'jquery',
            jQuery: 'jquery',
            moment: 'moment'
        }),
        extractStylesheet,
        /*new webpack.LoaderOptionsPlugin({
            minimize: inProduction
        }),*/
        new webpack.optimize.CommonsChunkPlugin({
            name: 'vendor',
            minChunks: Infinity
        }),
        /*new PurifyCSSPlugin({
            paths: glob.sync([
                path.join(__dirname, 'app/templates/!*.latte'),
                path.join(__dirname, 'app/templates/!**!/!*.latte')
            ]),
            minimize: inProduction
        }),*/
        new CleanWebpackPlugin(['www/*/main.*', 'www/*/vendor.*'], {
            root: __dirname,
            verbose: true,
            dry: false
        }),
        new BuildManifestPlugin()
    ]

    if (production) {
        plugins.push(new webpack.optimize.UglifyJsPlugin({
            compress: {
                warnings: false
            }
        }))
    }

    return {
        entry: {
            main: [
                './app/assets/js/main.js',
                './app/assets/styles/main.scss'
            ],
            vendor: [
                'jquery',
                './www/js/jquery/jquery.tinytips.js',
                'bootstrap',
                './node_modules/bootstrap-ui/dist/js/bootstrap-ui.js',
                'moment',
                'eonasdan-bootstrap-datetimepicker'
            ]
        },
        output: {
            path: path.resolve(__dirname, 'www'),
            publicPath: '/srazvs/www/',
            filename: 'js/[name].[chunkhash].js'
        },
        resolve: {
            alias: [{
                // Force all modules to use the same jquery version.
                alias: 'jquery',
                name: path.join(__dirname, 'node_modules/jquery/src/jquery')
            }]
        },
        watchOptions: {
            poll: true
        },
        module: {
            rules: [
                {
                    test: /\.s[ac]ss$/,
                    use: extractStylesheet.extract(ExtractTextPluginConfig(minify))
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
        plugins,
        devtool: 'source-map',
        resolve: {
            alias: {
                recharts: 'recharts/es6'
            }
        }
    }
}
