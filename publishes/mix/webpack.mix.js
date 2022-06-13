const dotenvExpand = require('dotenv-expand');
dotenvExpand(require('dotenv').config({path: '../../.env'/*, debug: true*/}));
const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');
const tailwindcss = require('tailwindcss');
//const assetPath = process.env.NODE_ENV == 'production' ?'dev': 'dev';
const assetPath = "assets";
mix.setPublicPath('../../public').mergeManifest();


mix.sass(__dirname + '/Resources/assets/sass/app.scss', `${assetPath}/css/admin.css`)
    .options({
        processCssUrls: false,
        postCss: [tailwindcss(__dirname + '/tailwind.config.js')],
    });

mix.js(__dirname + '/Resources/assets/js/app.js', `${assetPath}/js/admin.js`);
if (mix.inProduction()) {
    mix.version();
}
mix.browserSync(process.env.APP_URL);
