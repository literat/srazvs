const fs = require('fs')
const path = require('path')

function BuildManifestPlugin () {}

BuildManifestPlugin.prototype.apply =  compiler => {
    compiler.plugin('emit', (compiler, callback) => {
        const manifest = JSON.stringify(compiler.getStats().toJson().assetsByChunkName)

        compiler.assets['manifest.json'] = {
            source: () => {
                return manifest
            },
            size: () => {
                return manifest.length
            }
        };

        callback()
    })
}

module.exports = BuildManifestPlugin;
