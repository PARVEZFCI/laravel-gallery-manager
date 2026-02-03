<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  image: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'update', 'delete', 'download'])

const editing = ref(false)
const deleting = ref(false)
const title = ref('')
const description = ref('')

watch(() => props.image, (value) => {
  if (value) {
    title.value = value.title || ''
    description.value = value.description || ''
    editing.value = false
  }
})

const updateImage = async () => {
  try {
    const response = await axios.put(`/api/gallery/images/${props.image.id}`, {
      title: title.value,
      description: description.value
    })

    if (response.data.success) {
      emit('update', response.data.data)
      editing.value = false
    }
  } catch (error) {
    console.error('Error updating image:', error)
    alert('Error updating image')
  }
}

const deleteImage = async () => {
  if (!confirm('Are you sure you want to delete this image?')) {
    return
  }

  deleting.value = true

  try {
    const response = await axios.delete(`/api/gallery/images/${props.image.id}`)

    if (response.data.success) {
      emit('delete', props.image.id)
    }
  } catch (error) {
    console.error('Error deleting image:', error)
    alert('Error deleting image')
  } finally {
    deleting.value = false
  }
}

const downloadImage = () => {
  emit('download', props.image)
}
</script>

<template>
  <div
    v-if="show && image"
    class="fixed inset-0 z-50 overflow-y-auto"
    @click.self="$emit('close')"
  >
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
      <!-- Backdrop -->
      <div class="fixed inset-0 transition-opacity bg-black bg-opacity-75" @click="$emit('close')" />

      <!-- Modal -->
      <div class="relative inline-block w-full max-w-5xl overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle">
        <!-- Close Button -->
        <div class="absolute top-4 right-4 z-10">
          <button
            @click="$emit('close')"
            class="p-2 text-white bg-black bg-opacity-50 rounded-full hover:bg-opacity-75 transition"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2">
          <!-- Image Preview -->
          <div class="relative bg-gray-900 flex items-center justify-center p-8">
            <img
              :src="image.url"
              :alt="image.title || image.original_filename"
              class="max-w-full max-h-[70vh] object-contain"
            />
          </div>

          <!-- Image Details -->
          <div class="p-6 overflow-y-auto max-h-[70vh]">
            <div class="space-y-6">
              <!-- Header -->
              <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Image Details</h2>
                <p class="text-sm text-gray-500">{{ image.uploaded_at ? new Date(image.uploaded_at).toLocaleString() : '' }}</p>
              </div>

              <!-- Editable Fields -->
              <div v-if="editing" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                  <input
                    v-model="title"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Enter title"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                  <textarea
                    v-model="description"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Enter description"
                  />
                </div>

                <div class="flex gap-2">
                  <button
                    @click="updateImage"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                  >
                    Save Changes
                  </button>
                  <button
                    @click="editing = false"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"
                  >
                    Cancel
                  </button>
                </div>
              </div>

              <!-- Display Mode -->
              <div v-else class="space-y-4">
                <div>
                  <h3 class="text-sm font-medium text-gray-500 mb-1">Title</h3>
                  <p class="text-gray-900">{{ image.title || 'No title' }}</p>
                </div>

                <div>
                  <h3 class="text-sm font-medium text-gray-500 mb-1">Description</h3>
                  <p class="text-gray-900">{{ image.description || 'No description' }}</p>
                </div>
              </div>

              <!-- Image Info -->
              <div class="border-t border-gray-200 pt-4">
                <h3 class="text-sm font-medium text-gray-900 mb-3">File Information</h3>
                <dl class="space-y-2">
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Filename</dt>
                    <dd class="text-sm text-gray-900 truncate ml-4">{{ image.original_filename }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Size</dt>
                    <dd class="text-sm text-gray-900">{{ image.formatted_size }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Dimensions</dt>
                    <dd class="text-sm text-gray-900">
                      {{ image.metadata?.width }} × {{ image.metadata?.height }}
                    </dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Type</dt>
                    <dd class="text-sm text-gray-900">{{ image.mime_type }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Storage</dt>
                    <dd class="text-sm text-gray-900">{{ image.disk }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Date</dt>
                    <dd class="text-sm text-gray-900">{{ image.folder_date }}</dd>
                  </div>
                </dl>
              </div>

              <!-- Tags -->
              <div v-if="image.tags && image.tags.length > 0" class="border-t border-gray-200 pt-4">
                <h3 class="text-sm font-medium text-gray-900 mb-2">Tags</h3>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="tag in image.tags"
                    :key="tag.id"
                    class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full"
                  >
                    {{ tag.name }}
                  </span>
                </div>
              </div>

              <!-- Actions -->
              <div class="border-t border-gray-200 pt-4 space-y-2">
                <button
                  v-if="!editing"
                  @click="editing = true"
                  class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Edit Details
                </button>

                <button
                  @click="downloadImage"
                  class="w-full px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition flex items-center justify-center gap-2"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                  </svg>
                  Download
                </button>

                <button
                  @click="deleteImage"
                  :disabled="deleting"
                  class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center justify-center gap-2 disabled:opacity-50"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  {{ deleting ? 'Deleting...' : 'Delete Image' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
