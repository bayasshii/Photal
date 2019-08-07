<template>
    <div>
        <div id="overlay" v-show="showContent" @click="closeModal">
        </div>
        <div class="modal__contents" v-show="showContent">
            <button @click="delete_album">
                    削除する
            </button>
            <!-- アルバムタイトル編集 -->
            <div>
                <label>アルバム名:
                    <input type="text" v-bind:value="album_name">
                </label>
            </div>

            <!-- アルバムメンバー編集 -->
            <div>
                <label>ともだち:
                    <select v-model="AlbumMembersSelected" multiple>
                        <option disabled value="">選択してね〜</option>
                        <option 
                            v-for="app_user in app_users" 
                            v-bind:value="app_user.github_id" 
                            v-if="app_user.github_id != github_user.nickname"
                        >
                            {{ app_user.github_id }}
                        </option>
                    </select>
                    <div class="flex">
                        <div class="album__mambersContainer--item">
                        {{github_user.nickname}}
                        </div>
                        <div
                            v-for="members in AlbumMembersSelected"
                            class="album__mambersContainer--item"
                            v-if="github_user.nickname != members"
                        >
                            {{members}}
                        </div>
                    </div>
                </label>
            </div>

            <button @click="upload_album">
                変更を保存する
            </button>

            <!-- 写真の追加 -->
            <div class="photo_input">
                <label>写真を選んでね
                    <input @change="selectedFile" ref="img_inp" type="file" accept="image/*" multiple>
                </label>
            </div>
            <button @click="postPhoto">
                写真送信！！！！！！！
            </button>
            <!-- アルバムフォト編集 -->
            <div class="album__imgsContainer flex">
                <div
                    v-for="album_photo in album_photos"
                    v-if="album_photo.album_id == album_id"
                    class="album__imgsContainer--item"
                >
                    <img
                        @click="delete_photo(album_photo.album_photo_id)"
                        :src="'https://bayashi.s3-ap-northeast-1.amazonaws.com/' + album_photo.album_photo_id"
                        v-bind:class='{delete_img: album_delete_photos.filter(id => id == album_photo.album_photo_id).length}'
                    >
                </div>
            </div>
            <div v-if="album_delete_photos.length">
                <button @click="delete_photos">写真の削除(ゴミ箱)(モーダルのフッターみたいに)</button>
            </div>

        </div>
    </div>
</template>

<script>
    import {
        disableBodyScroll,
        enableBodyScroll,
        clearAllBodyScrollLocks
    } from 'body-scroll-lock';

    export default {
        data() {
            return {
                showContent: false.axios,
                album_id: "",
                album_name: "",
                AlbumName: "",
                album_members: "",
                album_photos: "",
                AlbumMembersSelected: [],
                app_users: [],
                github_user: [],
                album_delete_photos: []
            }
        }
        ,
        methods: {
            getInfo: function(album_id) {
                var data = {
                    album_id: album_id,
                }
                var self = this;
                axios.post('api/photal/getSelfData', data)
                .then(res =>  {
                    self.album_name = res.data.album_name;
                    self.album_members = res.data.album_members;
                    self.album_photos = res.data.album_photos;
                    self.app_users = res.data.app_users;
                    self.github_user = res.data.github_user;

                    // デフォルトのメンバーを代入
                    self.AlbumMembersSelected=[]
                    self.album_members.forEach(function( member ){
                        var member_name = member.album_member 
                        self.AlbumMembersSelected.push(member_name)
                    })
                })
            }
            ,
            selectedFile: function(e) {
                // 選択された File の情報を保存しておく
                e.preventDefault();
                let files = e.target.files;
                this.uploadFile = files;
            }
            ,
            postPhoto: function() {
                // 良い感じに画像送信するためのあれこれ
                const api = axios.create();
                var array = []
                let config = {
                    headers: {
                        'content-type': 'multipart/form-data'
                    }
                }
                for (var i = 0; i < this.uploadFile.length; i++) {
                    let formData = new FormData();
                    formData.append('image', this.uploadFile[i]);
                    formData.append('album_id', this.album_id);
                    array.push(api
                        .post('api/photalTest', formData, config)
                    )
                }
                // 再読み込み
                Promise.all(array).then(
                    function(){
                        this.getInfo(this.album_id)
                        // フォームの初期化
                        this.uploadFile = null
                    }.bind(this)
                )
            }
            ,
            delete_album: function(){
                var data = {
                    album_id: this.album_id
                }
                axios.post('api/photal/delete', data)
                this.$emit('update')
                this.closeModal()
            }
            ,
            delete_photo: function(album_photo_id) {
                if (this.album_delete_photos.filter(id => id == album_photo_id).length) {
                    this.album_delete_photos = this.album_delete_photos.filter(id=>id!==album_photo_id)
                }
                else {
                    this.album_delete_photos.push(album_photo_id)
                }
            }
            ,
            delete_photos: function(album_photo_id){
                var data = {
                    album_delete_photos: this.album_delete_photos
                }
                axios
                .post('api/photal/deletePhotos', data)
                .then(res =>  {
                    this.$emit('update')
                    this.getInfo(this.album_id)
                })
            }
            ,
            upload_album:function(){
                var data = {
                    album_id: this.album_id,
                    album_name: this.album_name,
                    album_members: this.AlbumMembersSelected
                }
                console.log("---------------------")
                axios
                .post('api/photal/upload', data)
                .then(res =>  {
                    console.log("いけたっぽい！")
                    this.$emit('update')
                    this.getInfo(this.album_id)
                })
            }
            ,
            openModal: function(album_id){
                this.showContent = true
                this.getInfo(album_id)
                this.album_id = album_id

                const modal = document.querySelector('#modal')
                disableBodyScroll(modal)
            },
            closeModal: function(){
                this.showContent = false
                clearAllBodyScrollLocks()
            }
        }
    }
</script>