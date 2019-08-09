<template>
    <div class="album__container">
        <div class="album__header">
            <div class="album__header--title">
                <h2>{{album_name}}</h2>
            </div>
            <!--メンバー表示-->
            <div class="album__header--members flex flex-wrap">
                <div 
                    v-for="am in album_members"
                    class="album__header--member"
                >
                    <router-link
                        :to="'/photal/home/'+am.album_member"
                    >
                    {{am.album_member}}
                    </router-link>
                </div>
            </div>
            <!--メニュー-->
            <div
                v-for="am in album_members"
                v-if="am.album_id == albumid"
            >
                <div
                    v-if="am.album_member == github_user.nickname"
                    class="albumMenu"
                >
                    <AlbumMenuComponent
                        :album_id = albumid
                        @update="getInfo(album_id)"
                    />
                </div>
            </div>
        </div>
        <!--写真表示-->
        <div class="album__imgsContainer flex">
            <div
                v-for="(album_photo, index) in album_photos"
                v-if="index < count"
                class="album__imgsContainer--item"
            >
                <img
                    :src="'https://bayashi.s3-ap-northeast-1.amazonaws.com/' + album_photo.album_photo_id"
                    @click="loveBtn(album_photo.album_photo_id, github_user.id)"
                >
                <div
                    class="album__imgsContainer--love"
                    @click="loveBtn(album_photo.album_photo_id, github_user.id)"
                >
                    <div class="love__contents flex">
                        <div
                            class="inline-box"
                            v-if="your_love_photos.filter(x=>x.album_photo_id == album_photo.album_photo_id).length"
                        >
                            <img src="https://bayashi.s3-ap-northeast-1.amazonaws.com/blackheart.png">
                        </div>
                        <div
                            class="inline-box"
                            v-else
                        >
                            <img src="https://bayashi.s3-ap-northeast-1.amazonaws.com/whiteheart.png">
                        </div>
                        <div 
                            v-for="love_count in love_counts"
                            v-if="album_photo.album_photo_id == love_count.album_photo_id"
                        >
                            {{love_count.love_count}}
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="albumBtn--show open"
                @click="showMore"
                v-if="album_photos.length > count"
            >
                もっと見る
            </div>
            <div
                class="albumBtn--show close"
                @click="showLess"
                v-if="count>12"
            >
                閉じる
            </div>
        </div>
    </div>
</template>

<script>
import AlbumMenuComponent from './AlbumMenuComponent.vue'
    export default {
        props: {
            albumid: Number,
        }
        ,
        components: {
            AlbumMenuComponent
        }
        ,
        data() { 
            //データの初期値を設定
            return {
                album_id: "",
                album_name: "",
                album_members: "", 
                album_photos: "",
                love_counts: "",
                github_user:"",
                your_love_photos:[],
                count: 12
            };
        }
        ,
        methods: {
            // 情報取得
            getInfo: function(albumid) {
                console.log("------------------------")
                console.log("データ取りに行くで！")
                console.log("------------------------")
                var data = {
                    album_id: albumid
                }
                this.album_id = albumid
                var self = this;
                axios
                .post('/api/photal/getSelfData', data)
                .then(res =>  {

                    self.album_name = res.data.album_name

                    console.log("------------------------")
                    console.log('アルバムname')
                    console.log(res.data.album_name)
                    console.log("------------------------")

                    self.album_members = res.data.album_members
                    // console.log('アルバムめんば')
                    // console.log(this.album_members)
                    self.album_photos = res.data.album_photos
                    self.app_users = res.data.app_users
                    self.github_user = res.data.github_user
                    // いいねのデータ
                    this.getLoveInfo(this.github_user.id)
                })
            }
            ,
            showMore: function(){
                this.count += 12;
            }
            ,
            showLess:function(){
                this.count = 12;
            }
            ,
            getLoveInfo: function(github_id) {
                var self = this;
                var data = {
                    github_id: github_id
                }
                axios.post('/api/photal/loveInfo',data).then(res =>  {
                    self.love_counts = res.data.love_counts;
                    self.your_love_photos = res.data.your_love_photos;
                    
                })
            }
            ,
            // 良いねボタン
            loveBtn: function(albumPhotoId,githubId) {
                var data = {
                    album_photo_id: albumPhotoId,
                    github_id: githubId
                }
                axios.post('/api/photal/love', data)
                .then(res =>  {
                    this.love_counts = res.data.love_counts;
                    this.your_love_photos = res.data.your_love_photos;
                })
            },
            // 新規投稿された時読み込みさせる
            toChild: function() {
                this.getInfo(this.albumid);
                
            }
        },
        created() {
            this.getInfo(this.albumid);
        }
    }
</script>