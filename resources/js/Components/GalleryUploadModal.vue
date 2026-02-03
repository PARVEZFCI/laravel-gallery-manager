<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  folder: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const files = ref([])
const previews = ref([])
const uploading = ref(false)
const uploadProgress = ref(0)
const title = ref('')
const description = ref('')
const uploadDate = ref(new Date().toISOString().split('T')[0])
const disk = ref('public')

watch(() => props.show, (value) => {
  if (!value) {
    resetForm()
  } else if (props.folder) {
    uploadDate.value = props.folder.folder_date
  }
})

const handleFileSelect = (event) => {
  const selectedFiles = Array.from(event.target.files)
  files.value = selectedFiles
  
  // Generate previews
  previews.value = []
  selectedFiles.forEach(file => {
    const reader = new FileReader()
    reader.onload = (e) => {
      previews.value.push({
        file: file,
        url: e.target.result,
        name: file.name,
        size: formatFileSize(file.size)
      })
    }
    reader.readAsDataURL(file)
  })
}

const removeFile = (index) => {
  files.value.splice(index, 1)
  previews.value.splice(index, 1)
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

const uploadImages = async () => {
  if (files.value.length === 0) {
    alert('Please select at least one image')
    return
  }

  uploading.value = true
  uploadProgress.value = 0

  const formData = new FormData()
  
  files.value.forEach((file, index) => {
    formData.append(`images[${index}]`, file)
  })
  
  if (title.value) formData.append('title', title.value)
  if (description.value) formData.append('description', description.value)
  formData.append('date', uploadDate.value)
  formData.append('disk', disk.value)

  try {
    const response = await axios.post('/api/gallery/images/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: (progressEvent) => {
        uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total)
      }
    })

    if (response.data.success) {
      emit('success', response.data.data)
      resetForm()
    }
  } catch (error) {
    console.error('Upload error:', error)
    alert(error.response?.data?.message || 'Error uploading images')
  } finally {
    uploading.value = false
    uploadProgress.value = 0
  }
}

const resetForm = () => {
  files.value = []
  previews.value = []
  title.value = ''
  description.value = ''
  uploadDate.value = new Date().toISOString().split('T')[0]
  disk.value = 'public'
  uploading.value = false
  uploadProgress.value = 0
}
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 overflow-y-auto"
    @click.self="$emit('close')"
  >
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
      <!-- Backdrop -->
      <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="$emit('close')" />

      <!-- Modal -->
      <div class="relative inline-block w-full max-w-2xl px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:p-6">
        <div class="absolute top-0 right-0 pt-4 pr-4">
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-500"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="sm:flex sm:items-start">
          <div class="w-full mt-3 text-center sm:mt-0 sm:text-left">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
              Upload Images
            </h3>

            <div class="mt-4 space-y-4">
              <!-- File Input -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Select Images
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-500 transition">
                  <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                      <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex text-sm text-gray-600">
                      <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                        <span>Upload files</span>
                        <input
                          type="file"
                          multiple
                          accept="image/*"
                          class="sr-only"
                          @change="handleFileSelect"
                        />
                      </label>
                      <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
                  </div>
                </div>
              </div>

              <!-- Preview -->
              <div v-if="previews.length > 0" class="grid grid-cols-3 gap-4">
                <div
                  v-for="(preview, index) in previews"
                  :key="index"
                  class="relative group"
                >
                  <img
                    :src="preview.url"
                    :alt="preview.name"
                    class="w-full h-32 object-cover rounded-lg"
                  />
                  <button
                    @click="removeFile(index)"
                    class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                  <div class="mt-1 text-xs text-gray-600 truncate">
                    {{ preview.name }} ({{ preview.size }})
                  </div>
                </div>
              </div>

              <!-- Form Fields -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Title (Optional)
                </label>
                <input
                  v-model="title"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Enter image title"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Description (Optional)
                </label>
                <textarea
                  v-model="description"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Enter image description"
                />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Upload Date
                  </label>
                  <input
                    v-model="uploadDate"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Storage
                  </label>
                  <select
                    v-model="disk"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  >
                    <option value="public">Local (Public)</option>
                    <option value="s3">AWS S3</option>
                  </select>
                </div>
              </div>

              <!-- Upload Progress -->
              <div v-if="uploading" class="mt-4">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-sm text-gray-700">Uploading...</span>
                  <span class="text-sm text-gray-700">{{ uploadProgress }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-blue-600 h-2 rounded-full transition-all"
                    :style="{ width: uploadProgress + '%' }"
                  />
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex justify-end gap-3">
              <button
                @click="$emit('close')"
                :disabled="uploading"
                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition disabled:opacity-50"
              >
                Cancel
              </button>
              <button
                @click="uploadImages"
                :disabled="uploading || files.length === 0"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
              >
                {{ uploading ? 'Uploading...' : 'Upload' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
