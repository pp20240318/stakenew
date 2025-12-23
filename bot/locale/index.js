import en from './en.json'//英语语言包
import zhHans from './zh-Hans.json' // 中文简体
import zhHant from './zh-Hant.json' // 中文繁体

import alby from './阿拉伯语.json' // 中文繁体
import bly from './波兰语.json' // 中文繁体
import dy from './德语.json' // 中文繁体
import fy from './法语.json' // 中文繁体
import hy from './韩语.json' // 中文繁体
import ptyy from './葡萄牙语.json' // 中文繁体
import ry from './日语.json' // 中文繁体
import ty from './泰语.json' // 中文繁体
import trqy from './土耳其语.json' // 中文繁体
import wkly from './乌克兰语.json' // 中文繁体
import xbyy from './西班牙语.json' // 中文繁体
import ydly from './意大利语.json' // 中文繁体
import ydy from './印地语.json' // 中文繁体
//import es from './es.json' // 西班牙语言包
import { createI18n } from "vue-i18n"; 

// 安全获取语言设置
function getLanguage() {
  try {
    // 首先尝试使用 uni 的API（在App环境中）
    try {
      const value = uni.getStorageSync("userlanguage");
      if (value) return value;
    } catch (e) {
      console.error("uni storage access failed:", e);
    }

    // 然后尝试使用 localStorage（在Web环境中）
    if (typeof localStorage !== 'undefined' && localStorage) {
      const value = localStorage.getItem("userlanguage");
      if (value) return value;
    }
    
    return "en";
  } catch (e) {
    console.error("获取语言设置失败:", e);
    return "en";
  }
}

const i18n = createI18n({
  locale: getLanguage(), // 使用安全函数获取语言标识
  globalInjection: true, // 全局注入,可以直接使用$t
  allowComposition: true,
  legacy: false, // 设置为false可以解决ESM构建警告
  messages: {
    en, 
    zhHant,
    alby,
    bly,
    dy,
    fy,
    hy,ptyy,ry,ty,trqy,wkly,xbyy,ydly,ydy
  }
}) 
 
export default i18n