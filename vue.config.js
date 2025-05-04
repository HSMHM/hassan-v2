module.exports = {
  publicPath: process.env.NODE_ENV === 'production' ? '/hassan-v2/' : '/',
  lintOnSave: false,
  devServer: {
    port: 8080,
    open: true,
  },
};