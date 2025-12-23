import { defineConfig } from 'vite'
import uni from '@dcloudio/vite-plugin-uni'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [uni()],
  // 添加 Vue I18n 相关配置，解决 ESM-bundler 警告
  // resolve: {
  //   alias: {
  //     'vue-i18n': 'vue-i18n/dist/vue-i18n.cjs.js'
  //   }
  // },
  define: {
    // 显式替换 Vue I18n 特性标志，用于获取正确的 tree-shaking
    __VUE_I18N_FULL_INSTALL__: true,
    __VUE_I18N_LEGACY_API__: false,
    __INTLIFY_PROD_DEVTOOLS__: false
  }
}) 