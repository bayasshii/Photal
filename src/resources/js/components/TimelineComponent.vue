<template>
    <div>
        新規アルバム作成
        <CreateAlbumComponent
            @callParent = "callFromChild"
        />
        タイムライン表示
        <ShowAlbumComponent
            ref = "hoge"
        />
    </div>
</template>

<script>
    import CreateAlbumComponent from './CreateAlbumComponent.vue'
    import ShowAlbumComponent from './ShowAlbumComponent.vue'

    export default {
        components: {
            CreateAlbumComponent,
            ShowAlbumComponent,
        },
        methods: {
            callFromChild () {
                // 新規投稿を受け取ったら,
                // 表示データの再読み込みさせる.
                this.$refs.hoge.toChild()
            }
        }
    }
</script>