<template>
    <div>
        <div class="header flex">
            <div class="header__item"><router-link :to="'/photal/home/'+nickname">Home</router-link></div>
            <div class="header__item">Timeline</div>
            <div class="header__item hello">こんにちは、<router-link :to="'/photal/home/'+nickname"> {{nickname}}</router-link> さん</div>
        </div>
        
        <CreateAlbumComponent
            @callParent = "callFromChild"
        />
        
        <div
            v-for="(album, index) in computedAlbums"
            v-if="index < count"
        >
            <ShowAlbumComponent
                ref = albumcomponent 
                :albumid = computedAlbums[index].album_id
            />
        </div>
        <div
            class="albumBtn--show"
            @click="showMore"
            v-if="computedAlbums.length > count"
        >
            もっと見る
        </div>
    </div>
</template>

<script>
    import CreateAlbumComponent from './CreateAlbumComponent.vue'
    import ShowAlbumComponent from './ShowAlbumComponent.vue'

    export default {
        data() { 
             //データの初期値を設定
            return {
                count: 5,
                albums: "",
                nickname:"noname"
            };
        }
        ,
        components: {
            CreateAlbumComponent,
            ShowAlbumComponent,
        },
        computed: {
            computedAlbums() {
                var albums = this.albums
                return albums
            }
        }
        ,
        methods: {
            showMore: function(){
                this.count += 5;
            }
            ,
            getAlbumId(){
                axios
                .get('/api/photal/github')
                .then(res =>  {
                    this.nickname = res.data.github_user.nickname;
                })

                axios
                .get('/api/photal/albumId')
                .then(res =>  {
                    this.albums = res.data.albums.reverse();
                    console.log("---------album--------")
                    console.log(this.albums)
                    console.log("--------album---------")
                })
            }
            ,
            callFromChild () {
                // 新規投稿を受け取ったら,
                // 表示データの再読み込みさせる.
                this.albums = []
                this.getAlbumId()
            }
        }
        ,
        created() {
            // APIアクセスでgetInfo()を呼び出す
            this.getAlbumId()
        }
    }
</script>