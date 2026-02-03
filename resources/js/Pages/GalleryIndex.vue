<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import GalleryFolderCard from '../Components/GalleryFolderCard.vue'
import GalleryImageGrid from '../Components/GalleryImageGrid.vue'
import GalleryUploadModal from '../Components/GalleryUploadModal.vue'
import GalleryImageModal from '../Components/GalleryImageModal.vue'

const props = defineProps({
  folders: {
    type: Array,
    default: () => []
  }
})

const selectedFolder = ref(null)
const images = ref([])
const loading = ref(false)
const showUploadModal = ref(false)
const showImageModal = ref(false)
const selectedImage = ref(null)
const selectedImages = ref([])
const searchQuery = ref('')
const viewMode = ref('folders') // 'folders' or 'images'

const filteredFolders = computed(() => {
  if (!searchQuery.value) return props.folders
  return props.folders.filter(folder => 
    folder.formatted_date.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const loadFolderImages = async (folder) => {
  selectedFolder.value = folder
  viewMode.value = 'images'
  loading.value = true

  try {
    const response = await axios.get('/api/gallery/images', {
      params: {
        folder_date: folder.folder_date
      }
    })
    images.value = response.data.data
  } catch (error) {
    console.error('Error loading images:', error)
  } finally {
    loading.value = false
  }
}

const backToFolders = () => {
  viewMode.value = 'folders'
  selectedFolder.value = null
  images.value = []
  selectedImages.value = []
}

const handleUploadSuccess = () => {
  showUploadModal.value = false
  if (selectedFolder.value) {
    loadFolderImages(selectedFolder.value)
  }
  router.reload({ only: ['folders'] })
}

const openImageModal = (image) => {
  selectedImage.value = image
  showImageModal.value = true
}

const handleImageUpdate = (updatedImage) => {
  const index = images.value.findIndex(img => img.id === updatedImage.id)
  if (index !== -1) {
    images.value[index] = updatedImage
  }
}

const handleImageDelete = (imageId) => {
  images.value = images.value.filter(img => img.id !== imageId)
  showImageModal.value = false
  selectedImage.value = null
  router.reload({ only: ['folders'] })
}

const toggleImageSelection = (image) => {
  const index = selectedImages.value.findIndex(img => img.id === image.id)
  if (index !== -1) {
    selectedImages.value.splice(index, 1)
  } else {
    selectedImages.value.push(image)
  }
}

const handleBulkDelete = async () => {
  if (!confirm(`Are you sure you want to delete ${selectedImages.value.length} image(s)?`)) {
    return
  }

  try {
    await axios.post('/api/gallery/images/bulk-delete', {
      image_ids: selectedImages.value.map(img => img.id)
    })
    
    selectedImages.value.forEach(img => {
      images.value = images.value.filter(i => i.id !== img.id)
    })
    
    selectedImages.value = []
    router.reload({ only: ['folders'] })
  } catch (error) {
    console.error('Error deleting images:', error)
    alert('Error deleting images')
  }
}

const downloadImage = async (image) => {
  try {
    const response = await axios.get(`/api/gallery/images/${image.id}/download`, {
      responseType: 'blob'
    })
    
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', image.original_filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    console.error('Error downloading image:', error)
    alert('Error downloading image')
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Gallery Manager</h1>
            <p class="text-gray-600 mt-1">
              {{ viewMode === 'folders' ? 'Browse your folders' : selectedFolder?.formatted_date }}
            </p>
          </div>
          <button
            @click="showUploadModal = true"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Upload Images
          </button>
        </div>

        <!-- Navigation and Search -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <button
              v-if="viewMode === 'images'"
              @click="backToFolders"
              class="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Back to Folders
            </button>

            <div v-if="selectedImages.length > 0" class="flex items-center gap-2">
              <span class="text-sm text-gray-600">{{ selectedImages.length }} selected</span>
              <button
                @click="handleBulkDelete"
                class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition"
              >
                Delete Selected
              </button>
              <button
                @click="selectedImages = []"
                class="px-3 py-1 bg-gray-300 text-gray-700 text-sm rounded hover:bg-gray-400 transition"
              >
                Clear
              </button>
            </div>
          </div>

          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search..."
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            <svg
              class="absolute left-3 top-2.5 w-5 h-5 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
          </div>
        </div>
      </div>

      <!-- Folders View -->
      <div v-if="viewMode === 'folders'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <GalleryFolderCard
          v-for="folder in filteredFolders"
          :key="folder.id"
          :folder="folder"
          @click="loadFolderImages(folder)"
        />

        <div v-if="filteredFolders.length === 0" class="col-span-full text-center py-12">
          <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
          </svg>
          <p class="text-gray-500">No folders found</p>
        </div>
      </div>

      <!-- Images View -->
      <div v-if="viewMode === 'images'">
        <GalleryImageGrid
          :images="images"
          :loading="loading"
          :selected-images="selectedImages"
          @image-click="openImageModal"
          @toggle-selection="toggleImageSelection"
          @download="downloadImage"
        />
      </div>

      <!-- Upload Modal -->
      <GalleryUploadModal
        :show="showUploadModal"
        :folder="selectedFolder"
        @close="showUploadModal = false"
        @success="handleUploadSuccess"
      />

      <!-- Image Detail Modal -->
      <GalleryImageModal
        :show="showImageModal"
        :image="selectedImage"
        @close="showImageModal = false"
        @update="handleImageUpdate"
        @delete="handleImageDelete"
        @download="downloadImage"
      />
    </div>
  </div>
</template>
