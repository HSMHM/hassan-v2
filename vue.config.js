module.exports = {
  css: {
    loaderOptions: {
      postcss: {
        postcssOptions: {
          plugins: [
            require('autoprefixer')(),
            require('postcss-rtlcss')({
              mode: 'override',
              ltrPrefix: '[dir="ltr"]',
              rtlPrefix: '[dir="rtl"]',
              processUrls: false,
              safeBothPrefix: true
            })
          ]
        }
      }
    }
  },
  chainWebpack: config => {
    // Configure url-loader for font files
    config.module
      .rule('fonts')
      .test(/\.(woff2?|eot|ttf|otf|svg)(\?.*)?$/)
      .use('url-loader')
      .loader('url-loader')
      .options({
        limit: 10000,
        name: 'fonts/[name].[hash:7].[ext]',
        publicPath: '/'
      });
  }
};
