/**
 * 语言工具函数
 * 提供安全的语言设置和获取功能
 */

import i18n from '../locale/index.js';

// 获取当前语言
export function getLanguage() {
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

// 设置语言
export function setLanguage(lang) {
  try {
    // 设置i18n语言
    i18n.global.locale = lang;
    
    // 保存到存储
    try {
      // 先尝试使用uni存储，因为在APP环境中这个更可靠
      try {
        uni.setStorageSync("userlanguage", lang);
      } catch (uniError) {
        console.error("uni storage设置失败:", uniError);
      }
      
      // 如果有localStorage可用，也设置它（web环境）
      if (typeof localStorage !== 'undefined' && localStorage) {
        localStorage.setItem("userlanguage", lang);
      }
    } catch (e) {
      console.error("保存语言设置失败:", e);
    }
    
    // 触发语言变更事件
    uni.$emit('language-changed');
    
    return true;
  } catch (e) {
    console.error("设置语言失败:", e);
    return false;
  }
}

export default {
  getLanguage,
  setLanguage
}; 