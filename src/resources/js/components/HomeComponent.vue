<template>
    <div>
        <div class="header flex">
            <div class="header__item">Home</div>
            <div class="header__item"><router-link to="/photal/timeline">Timeline</router-link></div>
            <div class="header__item hello">こんにちは、 {{mynickname}} さん</div>
        </div>
        <div class="profile__wrap">
            <div class="profile__contents flex">
                <div>
                    <img
                        class="profile--img"
                        :src="'https://github.com/'+nickname+'.png'"
                    />
                </div>
                <div class="profile__leftContents">
                    <div class="profile--title">
                        {{ nickname }}
                    </div>
                    <div class="profile--albumNumber flex">
                        <div>アルバム数　{{album_members.length}}</div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-for="(album, index) in album_members"
            v-if="index < count"
        >
            <ShowAlbumComponent
                :albumid = album_members[index].album_id
            />
        </div>
        <div
            class="albumBtn--show"
            @click="showMore"
            v-if="album_members.length > count"
        >
            もっと見る
        </div>
    </div>
</template>

<script>
    import ShowAlbumComponent from './ShowAlbumComponent.vue'
    export default {
        components: {
            ShowAlbumComponent,
        }
        ,
        data() { 
            //データの初期値を設定
            return {
                count: 5,
                nickname: this.$route.params.id,
                album_members: "",
                mynickname: "noname"
            };
        }
        ,
        methods: {
            showMore: function(){
                this.count += 5;
            }
            ,
            getHomeInfo: function(albumid) {

                axios
                .get('/api/photal/github')
                .then(res =>  {
                    this.mynickname = res.data.github_user.nickname;
                })

                var data = {
                    nickname: this.nickname
                }
                axios
                .post('/photal/api/photal/home', data)
                .then(res =>  {
                    this.album_members = res.data.album_members
                    console.log("メンバー情報取得完了〜〜")
                    console.log(this.album_members)
                })
            }
        },
        created(){
            this.getHomeInfo()
        }
    }
</script>