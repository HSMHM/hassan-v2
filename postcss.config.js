module.exports = {
  plugins: [
    require('postcss-rtlcss')({
      // Configuration options
      ltrPrefix: '[dir="ltr"]',
      rtlPrefix: '[dir="rtl"]',
      autoRename: false,
      stringMap: [
        {
          name: 'logical-properties',
          search: ['left', 'right'],
          replace: ['start', 'end'],
          options: { ignoreValues: false }
        }
      ]
    })
  ]
};