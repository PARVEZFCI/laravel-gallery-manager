<script setup>
const props = defineProps({
  images: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  selectedImages: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['image-click', 'toggle-selection', 'download'])

const isSelected = (image) => {
  return props.selectedImages.some(img => img.id === image.id)
}
</script>

<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <div
        v-for="i in 8"
        :key="i"
        class="aspect-square bg-gray-200 rounded-lg animate-pulse"
      />
    </div>

    <!-- Images Grid -->
    <div v-else-if="images.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <div
        v-for="image in images"
        :key="image.id"
        class="relative group aspect-square bg-gray-100 rounded-lg overflow-hidden cursor-pointer"
        :class="{ 'ring-4 ring-blue-500': isSelected(image) }"
      >
        <!-- Image -->
        <img
          :src="image.thumbnail_url"
          :alt="image.title || image.original_filename"
          class="w-full h-full object-cover transition group-hover:scale-105"
          @click="$emit('image-click', image)"
        />

        <!-- Selection Checkbox -->
        <div class="absolute top-2 left-2 z-10">
          <input
            type="checkbox"
            :checked="isSelected(image)"
            @click.stop="$emit('toggle-selection', image)"
            class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
          />
        </div>

        <!-- Quick Actions Overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition flex items-center justify-center opacity-0 group-hover:opacity-100">
          <div class="flex gap-2">
            <button
              @click.stop="$emit('image-click', image)"
              class="p-2 bg-white rounded-full hover:bg-gray-100 transition"
              title="View"
            >
              <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
            <button
              @click.stop="$emit('download', image)"
              class="p-2 bg-white rounded-full hover:bg-gray-100 transition"
              title="Download"
            >
              <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Image Info -->
        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-3 opacity-0 group-hover:opacity-100 transition">
          <p class="text-white text-sm font-medium truncate">{{ image.title || image.original_filename }}</p>
          <p class="text-white text-xs opacity-75">{{ image.formatted_size }}</p>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      <p class="text-gray-500">No images found in this folder</p>
    </div>
  </div>
</template>
