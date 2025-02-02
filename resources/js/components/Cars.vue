<template>
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6">Car Fleet Management</h1>

    <!-- Add/Edit Form -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-xl font-semibold mb-4">{{ editingCar ? 'Edit Car' : 'Add New Car' }}</h2>
      <form @submit.prevent="submitForm" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Brand Selection -->
          <div class="relative">
            <label class="block text-sm font-medium mb-1">Brand</label>
            <input
              type="text"
              v-model="brandSearch"
              class="w-full border rounded-md px-3 py-2"
              placeholder="Search brand..."
              @focus="showBrandDropdown = true"
            />
            <!-- Brand Dropdown -->
            <div
              v-if="showBrandDropdown"
              class="absolute z-10 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-y-auto"
            >
              <div
                v-for="make in filteredMakes"
                :key="make"
                @click="selectBrand(make)"
                class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
              >
                {{ make }}
              </div>
              <div
                v-if="filteredMakes.length === 0"
                class="px-3 py-2 text-gray-500"
              >
                No brands found
              </div>
            </div>
          </div>

          <!-- Model Selection -->
          <div class="relative">
            <label class="block text-sm font-medium mb-1">Model</label>
            <input
              type="text"
              v-model="modelSearch"
              class="w-full border rounded-md px-3 py-2"
              placeholder="Search model..."
              @focus="showModelDropdown = true"
              :disabled="!form.brand"
            />
            <!-- Model Dropdown -->
            <div
              v-if="showModelDropdown && form.brand"
              class="absolute z-10 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-y-auto"
            >
              <div
                v-for="model in filteredModels"
                :key="model"
                @click="selectModel(model)"
                class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
              >
                {{ model }}
              </div>
              <div
                v-if="filteredModels.length === 0"
                class="px-3 py-2 text-gray-500"
              >
                No models found
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Year</label>
            <input
              v-model="form.year"
              type="number"
              class="w-full border rounded-md px-3 py-2"
              required
              min="1900"
              :max="new Date().getFullYear() + 1"
            >
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Price</label>
            <input
              v-model="form.price"
              type="number"
              step="0.01"
              class="w-full border rounded-md px-3 py-2"
              required
              min="0"
            >
          </div>
        </div>

        <div class="flex justify-end space-x-3">
          <button
            v-if="editingCar"
            type="button"
            @click="cancelEdit"
            class="px-4 py-2 border rounded-md hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            :disabled="loading"
          >
            {{ editingCar ? 'Update' : 'Add' }} Car
          </button>
        </div>
      </form>
    </div>

    <!-- Cars Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
        <tr>
          <th
            @click="toggleSort('brand')"
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer"
          >
            Brand
            <span v-if="sortField === 'brand'" class="ml-1">
                {{ sortDirection === 'asc' ? '↑' : '↓' }}
              </span>
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
          <th
            @click="toggleSort('year')"
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer"
          >
            Year
            <span v-if="sortField === 'year'" class="ml-1">
                {{ sortDirection === 'asc' ? '↑' : '↓' }}
              </span>
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
        </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="car in cars" :key="car.id" class="hover:bg-gray-50">
          <td class="px-6 py-4">{{ car.brand }}</td>
          <td class="px-6 py-4">{{ car.model }}</td>
          <td class="px-6 py-4">{{ car.year }}</td>
          <td class="px-6 py-4">${{ formatPrice(car.price) }}</td>
          <td class="px-6 py-4 text-right space-x-2">
            <button
              @click="editCar(car)"
              class="text-blue-600 hover:text-blue-800"
            >
              Edit
            </button>
            <button
              @click="deleteCar(car.id)"
              class="text-red-600 hover:text-red-800"
            >
              Delete
            </button>
          </td>
        </tr>
        <tr v-if="cars.length === 0">
          <td colspan="5" class="px-6 py-4 text-center text-gray-500">
            No cars found
          </td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
      <div class="bg-white p-4 rounded-lg">Loading...</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'

// Refs for data
const cars = ref([])
const makes = ref([])
const models = ref([])
const loading = ref(false)
const editingCar = ref(null)

// Refs for search and dropdowns
const brandSearch = ref('')
const modelSearch = ref('')
const showBrandDropdown = ref(false)
const showModelDropdown = ref(false)

// Refs for sorting
const sortField = ref('year')
const sortDirection = ref('desc')

// Form data
const form = ref({
  brand: '',
  model: '',
  year: new Date().getFullYear(),
  price: ''
})

// Computed properties for filtering dropdowns
const filteredMakes = computed(() => {
  return makes.value.filter(make =>
    make.toLowerCase().includes(brandSearch.value.toLowerCase())
  )
})

const filteredModels = computed(() => {
  return models.value.filter(model =>
    model.toLowerCase().includes(modelSearch.value.toLowerCase())
  )
})

// API calls
const loadCars = async () => {
  try {
    loading.value = true
    const response = await axios.get('/api/cars', {
      params: {
        sort: sortField.value,
        direction: sortDirection.value
      }
    })
    cars.value = response.data
  } catch (error) {
    console.error('Error loading cars:', error)
    alert('Failed to load cars')
  } finally {
    loading.value = false
  }
}

const loadMakes = async () => {
  try {
    const response = await axios.get('/api/car-options/makes')
    makes.value = response.data.Results.map(make => make.Make_Name)
  } catch (error) {
    console.error('Error loading makes:', error)
  }
}

const loadModels = async () => {
  if (!form.value.brand) {
    models.value = []
    form.value.model = ''
    return
  }

  try {
    const response = await axios.get(`/api/car-options/models/${encodeURIComponent(form.value.brand)}`)
    models.value = response.data.Results.map(model => model.Model_Name)
    form.value.model = ''
    modelSearch.value = ''
  } catch (error) {
    console.error('Error loading models:', error)
  }
}

// Form actions
const submitForm = async () => {
  try {
    loading.value = true
    if (editingCar.value) {
      await axios.put(`/api/cars/${editingCar.value.id}`, form.value)
    } else {
      await axios.post('/api/cars', form.value)
    }
    await loadCars()
    resetForm()
  } catch (error) {
    console.error('Error submitting form:', error)
    alert(error.response?.data?.message || 'Failed to save car')
  } finally {
    loading.value = false
  }
}

// Selection handlers
const selectBrand = (make) => {
  form.value.brand = make
  brandSearch.value = make
  showBrandDropdown.value = false
  loadModels()
}

const selectModel = (model) => {
  form.value.model = model
  modelSearch.value = model
  showModelDropdown.value = false
}

// Edit/Delete handlers
const editCar = (car) => {
  editingCar.value = car
  form.value = { ...car }
  brandSearch.value = car.brand
  modelSearch.value = car.model
}

const cancelEdit = () => {
  resetForm()
}

const deleteCar = async (id) => {
  if (!confirm('Are you sure you want to delete this car?')) return

  try {
    loading.value = true
    await axios.delete(`/api/cars/${id}`)
    await loadCars()
  } catch (error) {
    console.error('Error deleting car:', error)
    alert('Failed to delete car')
  } finally {
    loading.value = false
  }
}

// Sort handler
const toggleSort = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'desc'
  }
  loadCars()
}

// Utility functions
const resetForm = () => {
  form.value = {
    brand: '',
    model: '',
    year: new Date().getFullYear(),
    price: ''
  }
  brandSearch.value = ''
  modelSearch.value = ''
  models.value = []
  editingCar.value = null
}

const formatPrice = (price) => {
  return Number(price).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

// Close dropdowns when clicking outside
const closeDropdowns = (event) => {
  if (!event.target.closest('.relative')) {
    showBrandDropdown.value = false
    showModelDropdown.value = false
  }
}

// Watch for brand changes
watch(() => form.value.brand, (newBrand) => {
  if (!newBrand) {
    brandSearch.value = ''
  }
})

// Lifecycle hooks
onMounted(() => {
  loadMakes()
  loadCars()
  document.addEventListener('click', closeDropdowns)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeDropdowns)
})
</script>

<style scoped>
.max-h-60 {
  max-height: 15rem;
}

th.cursor-pointer:hover {
  @apply bg-gray-100;
}
</style>