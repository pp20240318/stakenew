/**
 * 语言工具函数
 * 提供安全的语言设置和获取功能
 */

import i18n from '../locale/index.js';

// 获取当前语言
export function getLanguage() {
  try {
    if (typeof localStorage !== 'undefined') {
      return localStorage.getItem("userlanguage") || "en";
    }
    
    // 在App环境中使用uni-app的存储API
    try {
      const value = uni.getStorageSync("userlanguage");
      return value || "en";
    } catch (e) {
      console.error("获取存储失败:", e);
      return "en";
    }
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
      if (typeof localStorage !== 'undefined') {
        localStorage.setItem("userlanguage", lang);
      }
      
      uni.setStorageSync("userlanguage", lang);
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