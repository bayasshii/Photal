<template>
    <div>
        <div id="overlay" v-show="showContent" @click="closeModal">
        </div>
        <div class="modal__contents" v-show="showContent">
            <div class="tab_box">
            <ul class="tab_list flex">
                <li v-on:click="change('A')" v-bind:class="{'active': isActive === 'A'}">編集</li>
                <li v-on:click="change('B')" v-bind:class="{'active': isActive === 'B'}">写真を追加</li>
                <li v-on:click="change('C')" v-bind:class="{'active': isActive === 'C'}">写真を削除</li>
                <li class="deletealbum" v-on:click="change('D')" v-bind:class="{'active': isActive === 'D'}">アルバムを削除</li>
            </ul>
        
            <ul class="article">
                <li v-if="isActive === 'A'">
                    <!-- アルバムタイトル編集 -->
                    <div>
                        <div class="editItems--title">アルバム名</div>
                        <input class="albumName--input" type="text" v-model="album_name">
                    </div>

                    <!-- アルバムメンバー編集 -->
                    <div class="selecteFriend">
                        <div class="title">ともだちを選択してね</div>
                        <div class="title">現在、{{AlbumMembersSelected.length+1}}人 選択されています</div>
                        <label class="flex">
                            <div class="flex">
                                <div class="memberSelected">
                                    {{github_user.nickname}}
                                </div>
                            </div>
                            <div
                                v-for="app_user in app_users" 
                                v-if="app_user.github_id != github_user.nickname"
                                @click="addMember(app_user.github_id)"
                            >
                                <div
                                    v-if="AlbumMembersSelected.some( function( value ) {return value === app_user.github_id})"
                                    class="memberSelected"
                                >
                                    {{ app_user.github_id }}
                                </div>
                                <div
                                    v-else
                                    class="memberNotSelected"
                                >
                                    {{ app_user.github_id }}
                                </div>

                            </div>
                        </label>
                    </div>
                    <button class="button creatAlbum--submit" v-bind:disabled="isPush" @click="upload_album">
                        変更を保存する
                    </button>
                </li>
                <li v-else-if="isActive === 'B'">
                    <!-- 写真の追加 -->
                    <div class="photo_input mt-80">
                        <label class="albumImage--input">
                            写真を選んでね
                            <input @change="selectedFile" ref="img_inp" type="file" accept="image/*" multiple>
                        </label>
                        <div class="pt-10">現在、{{ uploadFile.length }}枚 選択されています</div>
                    </div>
                    <button v-bind:disabled="isPush" class="button creatAlbum--submit" @click="postPhoto">
                        写真を保存する
                    </button>
                </li>
                <li v-else-if="isActive === 'C'">
                    <!-- アルバムフォト編集 -->
                    <div>クリックして削除する写真を選択してください</div>
                    <div class="modal--images album__imgsContainer mt-0 flex">
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
                        <button v-bind:disabled="isPush" class="button creatAlbum--submit deletePhotoBtn" @click="delete_photos">
                            写真を削除する
                        </button>
                    </div>
                </li>
                <li v-else-if="isActive === 'D'">
                    <div class="button creatAlbum--submit deleteAlbumBtn" v-on:click="delete_album">
                        本当に削除しますか？
                    </div>
                </li>
            </ul>
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
                isPush: false,
                showContent: false.axios,
                album_id: "",
                album_name: "",
                album_members: "",
                album_photos: "",
                AlbumMembersSelected: [],
                app_users: [],
                github_user: [],
                album_delete_photos: [],
                isActive: "A",
                uploadFile: []
            }
        }
        ,
        methods: {
            addMember(member_id){
                if(this.AlbumMembersSelected.some( function( value ) {
                    return value === member_id})
                    ){
                    this.AlbumMembersSelected = this.AlbumMembersSelected.filter(function(a) {
                    return a !== member_id;
                    });
                }
                else{
                    this.AlbumMembersSelected.push(member_id)
                }
                console.log(this.AlbumMembersSelected)
            },
            change: function(num){
                this.isActive = num
            },
            pushBtn: function() {
                this.isPush = true;
            },
            getInfo: function(album_id) {
                var data = {
                    album_id: album_id,
                }
                var self = this;
                axios.post('/api/photal/getSelfData', data)
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
                        if(member_name != github_user.nickname){
                            self.AlbumMembersSelected.push(member_name)
                        }
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
                this.pushBtn()
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
                        .post('/api/photalTest', formData, config)
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
                axios.post('/api/photal/delete', data)
                this.$emit('update')
                this.closeModal()
            }
            ,
            delete_photo: function(album_photo_id) {
                this.pushBtn()
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
                .post('/api/photal/deletePhotos', data)
                .then(res =>  {
                    this.$emit('update')
                    this.getInfo(this.album_id)
                })
            }
            ,
            upload_album: function(){
                this.pushBtn();
                var data = {
                    album_id: this.album_id,
                    album_name: this.album_name,
                    album_members: this.AlbumMembersSelected
                }
                axios
                .post('/api/photal/upload', data)
                .then(res =>  {
                    this.closeModal();
                })
            }
            ,
            openModal: function(album_id){
                this.showContent = true
                this.getInfo(album_id)
                this.album_id = album_id
                const modal = document.querySelector('#modal')
                disableBodyScroll(modal)
            }
            ,
            closeModal: function(){
                this.$emit('update',this.album_id);
                this.showContent = false
                const modal = document.querySelector('#modal')
                enableBodyScroll(modal)
            }
        }
    }
</script>