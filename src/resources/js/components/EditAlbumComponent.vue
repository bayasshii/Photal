<template>
    <div>
        <div id="overlay" v-show="showContent" @click="closeModal">
        </div>
        <div class="modal__contents"  v-show="showContent">
            <!-- アルバムタイトル編集 -->
            <label>アルバム名:
                    <input type="text" v-bind:value="album_id">
            </label>
            <!-- アルバムメンバー編集 -->
            {{album_id}}<br>
            <!-- アルバムフォト編集 -->
            イェーイ<br>
            <button @click="delete_album">
                    削除する
            </button>
        </div>
    </div>
</template>

<script>
    export default {
        props:{
            album_id: Number,
        },
        data() {
            return {
                showContent: false
            }
        },
        methods: {
            getInfo: function(album_id) {
                var data = {
                    album_id: album_id,
                }
                var self = this;
                axios.post('api/photal/getSelfData', data)
                .then(res =>  {
                    console.log("やったぜ");
                    self.albums = res.data.albums;
                    self.album_members = res.data.album_members;
                    self.album_photos = res.data.album_photos;
                    self.love_counts = res.data.love_counts;
                    self.github_user = res.data.github_user;
                    self.your_love_photos = res.data.your_love_photos;
                })
            },
            delete_album: function(){
                var data = {
                    album_id: album_id,
                }
                axios.post('api/photal/delete', data)
                this.$emit('callParent')
            },
            openModal: function(){
                this.showContent = true
            },
            closeModal: function(){
                this.showContent = false
            }
        }
    }
</script>