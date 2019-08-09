<template>
    <div>
        <img
            class="editMenu"
            src="https://bayashi.s3-ap-northeast-1.amazonaws.com/threeeee.png"
            @click="Click(album_id)"
        />
        <EditAlbumComponent
            ref="modal"
            @update="update(album_id)"
        />
    </div>
</template>

<script>
    import EditAlbumComponent from './EditAlbumComponent.vue'
    export default {
        components: {
            EditAlbumComponent
        },
        props: {
            album_id: Number,
        },
        methods: {
            Click: function(album_id){
                this.$refs.modal.openModal(album_id)
            }
            ,
            update:function(album_id){
                this.$emit('update', album_id)
            }
        }
    }
</script>