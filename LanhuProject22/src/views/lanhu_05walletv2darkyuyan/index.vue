<template>
  <div class="page flex-col">
    <div class="box_1 flex-row">
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
    <div class="box_3 flex-col">
      <div class="group_1 flex-row justify-between">
        <img
          class="label_3"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPng18c3a5e6afe3f6d54f3ac5e2a6810a067bd0b75ff8906e76a2bf55b5a7e49089.png"
        />
        <span class="text_2">语言</span>
      </div>
      <div class="group_2 flex-row">
        <div class="image-text_3 flex-row justify-between">
          <img
            class="thumbnail_3"
            referrerpolicy="no-referrer"
            src="./assets/img/SketchPngca63f70cf3dd1235225e0950df974c26ed86ef0276d024290489c5301177f29b.png"
          />
          <span class="text-group_1">Search</span>
        </div>
      </div>
      <div class="list_2 flex-col">
        <div
          class="list-items_1 flex-row justify-between"
          :style="{ background: item.lanhuBg0 }"
          v-for="(item, index) in loopData0"
          :key="index"
        >
          <div class="image-text_4 flex-row justify-between">
            <img class="label_2" referrerpolicy="no-referrer" :src="item.lanhuimage0" />
            <span class="text-group_2" v-html="item.lanhutext0"></span>
          </div>
          <img class="thumbnail_4" referrerpolicy="no-referrer" :src="item.lanhuimage1" />
        </div>
      </div>
      <div class="group_3 flex-col">
        <div class="text-wrapper_1 flex-col"><span class="text_3">保存</span></div>
        <img
          class="image_2"
          referrerpolicy="no-referrer"
          src="./assets/img/SketchPngd9ad59ab1247bd15163774acb490139393e303110063ff743b5433e99ab2e98f.png"
        />
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
          lanhuBg0: 'transparent',
          lanhuimage0:
            'https://lanhu-oss-2537-2.lanhuapp.com/SketchPngb480d7dd127763475445101d0348fe012b7ac87d3b6fca6cdec55a780aee9133',
          lanhutext0: 'English',
          lanhuimage1:
            'https://lanhu-oss-2537-2.lanhuapp.com/SketchPng3b882156ec22104aeb427baaed3258d0f500e510b2181e50fd58ad2de959e57c'
        },
        {
          lanhuBg0:
            'url(https://lanhu-oss-2537-2.lanhuapp.com/SketchPngce149b0251d711572680bd75fb02f15075cc507e0a4ab28d558e521cf213dd5d) 100% no-repeat',
          lanhuimage0:
            'https://lanhu-oss-2537-2.lanhuapp.com/SketchPng9b60139f451693572b74dc28295a5ba8b0f668a43ad093d8102f322e07d1b60d',
          lanhutext0: 'Indonesian',
          lanhuimage1:
            'https://lanhu-oss-2537-2.lanhuapp.com/SketchPngda17bed02eb1ef306b429e68cbdefa46fc112f6f4cc7f4c038320c42e654ed4f'
        },
        {
          lanhuBg0:
            'url(https://lanhu-oss-2537-2.lanhuapp.com/SketchPngce149b0251d711572680bd75fb02f15075cc507e0a4ab28d558e521cf213dd5d) 100% no-repeat',
          lanhuimage0:
            'https://lanhu-oss-2537-2.lanhuapp.com/SketchPng131aa33b5728db8f35468f4ea4953db5d5205008bf56284ce52541c873a46598',
          lanhutext0: 'French',
          lanhuimage1:
            'https://lanhu-oss-2537-2.lanhuapp.com/SketchPngda17bed02eb1ef306b429e68cbdefa46fc112f6f4cc7f4c038320c42e654ed4f'
        },
        {
          lanhuBg0:
            'url(https://lanhu-oss-2537-2.lanhuapp.com/SketchPngce149b0251d711572680bd75fb02f15075cc507e0a4ab28d558e521cf213dd5d) 100% no-repeat',
          lanhuimage0:
            'https://lanhu-oss-2537-2.lanhuapp.com/SketchPngbe198ab8adae2dd824d0e29dbf98921bde414ae231817fb8dcd16de0cfdb00e2',
          lanhutext0: 'Chinese',
          lanhuimage1:
            'https://lanhu-oss-2537-2.lanhuapp.com/SketchPngda17bed02eb1ef306b429e68cbdefa46fc112f6f4cc7f4c038320c42e654ed4f'
        },
        {
          lanhuBg0:
            'url(https://lanhu-oss-2537-2.lanhuapp.com/SketchPngce149b0251d711572680bd75fb02f15075cc507e0a4ab28d558e521cf213dd5d) 100% no-repeat',
          lanhuimage0:
            'https://lanhu-oss-2537-2.lanhuapp.com/SketchPng801a5efa2a02f1ecaecedc71dce72785a1d77e2206ad2d7c971ca9370e25fd89',
          lanhutext0: 'Japanese',
          lanhuimage1:
            'https://lanhu-oss-2537-2.lanhuapp.com/SketchPngda17bed02eb1ef306b429e68cbdefa46fc112f6f4cc7f4c038320c42e654ed4f'
        }
      ],
      constants: {}
    };
  },
  methods: {}
};
</script>
<style scoped lang="css" src="./assets/index.css" />