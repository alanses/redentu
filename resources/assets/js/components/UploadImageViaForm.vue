<template>
    <div>
        <form v-on:submit.prevent="sendImageToServer">
            <div class="form-group">
                <label for="selectImage">Виберіть основне зображення</label>
                <p v-if="changeSuccess">Зображення успішно відправленно для обробки</p>
                <p v-if="changeErrors">Стались помилки</p>
                <input type="file"
                       @change="onFileChange"
                       class="form-control"
                       id="exampleInputEmail1"
                       aria-describedby="emailHelp"
                       placeholder="Забраження"
                       name="image"
                >
                <button
                        type="submit"
                        class="btn btn-primary"
                >Відправити</button>
            </div>
        </form>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                image: '',
                formData: '',
                changeSuccess: false,
                changeErrors: false
            }
        },
        methods: {
            onFileChange(e) {
                let files = e.target.files || e.dataTransfer.files
                if (!files.length)
                    return
                this.image = new FormData()
                this.image.append('myFile', files[0], files[0].name)
            },

            sendImageToServer(){
                this.$http.post('/save-image-as-watermark', this.image).then(res => {
                    if (res.status === 201) {
                        this.changeSuccess = true
                    }
                })
            }
        }

    }
</script>

<style scoped>

</style>