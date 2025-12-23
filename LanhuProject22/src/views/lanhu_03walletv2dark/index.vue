<template>
  <div class="page flex-col">
    <div class="group_1 flex-row">
      <span class="text_1">9:4</span>
      <img
        class="thumbnail_1"
        referrerpolicy="no-referrer"
        src="./assets/img/SketchPngf65c95bd53e8865c2739da431d0fcbd8f8d32b922b0075a874e28852620f4234.png"
      />
      <img
        class="thumbnail_2"
        referrerpolicy="no-referrer"
        src="./assets/img/SketchPng860e9ae07aac3ce17f06fffc6efeea5d79120a31892c10419baab39bb3c12905.png"
      />
      <img
        class="image_1"
        referrerpolicy="no-referrer"
        src="./assets/img/SketchPng6db081d5b6892e112c0ea07da6a61a7538ab31b290452a4d3692801cb40e06dd.png"
      />
    </div>
    <div class="section_3 flex-col">
      <div class="group_2 flex-row justify-between">
        <img
          class="label_10"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPng18c3a5e6afe3f6d54f3ac5e2a6810a067bd0b75ff8906e76a2bf55b5a7e49089.png"
        />
        <span class="text_2">钱包</span>
      </div>
      <div class="group_3 flex-row justify-between">
        <img
          class="image_2"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPng134dd0ad80dc962a3eb6b9a640c563be63a0e0fe2142e90bc1c7dc1e978d44ac.png"
        />
        <div class="image-text_27 flex-row justify-between">
          <div class="text-group_16 flex-col">
            <span class="text_3">Tocky&nbsp;Max</span>
            <span class="text_4">UID：560053991</span>
          </div>
          <img
            class="thumbnail_3"
            referrerpolicy="no-referrer"
            src="./assets/img/SketchPng897b54bb59ec1f3ccbb19a6b0fc10766a0a758f8931b2cb708f144550517e375.png"
          />
        </div>
      </div>
      <div class="box_11 flex-row justify-between">
        <div class="text-group_17 flex-col">
          <span class="text_7">My&nbsp;Accourt&nbsp;Balance(USDT)</span>
          <span class="text_8">Today’s&nbsp;Profit(USDT)</span>
          <span class="text_9">Total&nbsp;Profit(USDT)</span>
        </div>
        <div class="text-group_18 flex-col">
          <span class="text_10">11164.9000</span>
          <span class="text_11">0.00</span>
          <span class="text_12">0.00</span>
        </div>
      </div>
      <div class="list_1 flex-row">
        <div class="image-text_28 flex-col" v-for="(item, index) in loopData0" :key="index">
          <img class="label_11" referrerpolicy="no-referrer" :src="item.lanhuimage0" />
          <span class="text-group_19" v-html="item.lanhutext0"></span>
        </div>
      </div>
    </div>
    <div class="group_4 flex-col">
      <div class="box_12 flex-row justify-between">
        <div class="image-text_29 flex-row justify-between">
          <img
            class="label_3"
            referrerpolicy="no-referrer"
            src="./assets/img/SketchPng4f80f2e1fd1880a700241b35a562e0cfa7005f70dc46bba8619286558b32046b.png"
          />
          <span class="text-group_5">Personal&nbsp;Profile</span>
        </div>
        <img
          class="thumbnail_5"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPnge3ce23ad461cf68eca829ea63558d9553814402e948c2dfc61d703e55c349149.png"
        />
      </div>
      <div class="box_13 flex-row justify-between">
        <div class="image-text_30 flex-row justify-between">
          <img
            class="label_4"
            referrerpolicy="no-referrer"
            src="./assets/img/SketchPng8063a4308537f415a5efd1f6a73e73adcf448df7251967d3ea157ccd93a5c76b.png"
          />
          <span class="text-group_6">Invite&nbsp;Friends</span>
        </div>
        <img
          class="thumbnail_6"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPnge3ce23ad461cf68eca829ea63558d9553814402e948c2dfc61d703e55c349149.png"
        />
      </div>
      <div class="box_14 flex-row justify-between">
        <div class="image-text_31 flex-row justify-between">
          <img
            class="label_5"
            referrerpolicy="no-referrer"
            src="./assets/img/SketchPng1f36e9d68a3edfaf887edfc5eb204de0efe4ceb543a6299ec9090f07b68b2c13.png"
          />
          <span class="text-group_7">Language</span>
        </div>
        <div class="image-text_32 flex-row justify-between">
          <span class="text-group_8">English</span>
          <img
            class="thumbnail_7"
            referrerpolicy="no-referrer"
            src="./assets/img/SketchPnge3ce23ad461cf68eca829ea63558d9553814402e948c2dfc61d703e55c349149.png"
          />
        </div>
      </div>
      <div class="box_15 flex-row justify-between">
        <div class="image-text_33 flex-row justify-between">
          <img
            class="label_6"
            referrerpolicy="no-referrer"
            src="./assets/img/SketchPnga717520a29529fc7ddf2a66a592cc17e990e690bf3fd0bf143a8096eb6a067c5.png"
          />
          <span class="text-group_9">Settings</span>
        </div>
        <img
          class="thumbnail_8"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPnge3ce23ad461cf68eca829ea63558d9553814402e948c2dfc61d703e55c349149.png"
        />
      </div>
    </div>
    <div class="group_5 flex-row justify-around">
      <div class="image-text_34 flex-col">
        <img
          class="label_7"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPngfb9f6de92a387ce07f8aad6fd472fdbb42109c28bb466304f2b27180aeb67d08.png"
        />
        <span class="text-group_10">首页</span>
      </div>
      <div class="image-text_35 flex-col">
        <img
          class="label_8"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPng1291228781fb5ae0f5dda7da02c77c21b6073532326b150f8df394daba8ac003.png"
        />
        <span class="text-group_11">交易</span>
      </div>
      <div class="image-text_36 flex-col">
        <img
          class="label_9"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPng6c02a8acff3bd929a9e9964955f268c40458e28f24c81c88c9379d28103c0c4c.png"
        />
        <span class="text-group_12">钱包</span>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      loopData0: [
        {
          lanhuimage0:
            'https://lanhu-oss-2537-2.lanhuapp.com/SketchPnga43e9bc3c12fa2054a3ecc46fc8e0712aa012567dcac766a5876b0fb9090066b',
          lanhutext0: 'Recharge'
        },
        {
          lanhuimage0:
            'https://lanhu-oss-2537-2.lanhuapp.com/SketchPngf19ec681ceaae5cebe43993d05ac45a8441ee0ca27b817cacaeec17a1c8c4dd8',
          lanhutext0: 'Withdraw'
        },
        {
          lanhuimage0:
            'https://lanhu-oss-2537-2.lanhuapp.com/SketchPng7de67645702f7586b926e3eb03fec4f002307b4144ff0f044bc26c3f1115af2c',
          lanhutext0: 'Details'
        }
      ],
      constants: {}
    };
  },
  methods: {}
};
</script>
<style scoped lang="css" src="./assets/index.css" />