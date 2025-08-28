<template>
  <footer class="bg-gradient-to-r from-gray-900 via-blue-900 to-gray-900 text-white relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
      <div class="footer-pattern"></div>
    </div>
    
    <!-- Main Footer Content -->
    <div class="relative">
      <!-- Top Section -->
      <div class="border-b border-white/10 py-12">
        <div class="container mx-auto px-4">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand Section -->
            <div class="md:col-span-2">
                <a href="/" class="flex items-center group mb-6">
                  <img 
                    src="/images/Vheego.png" 
                    alt="Vheego Logo" 
                    class="h-20 w-auto transition-transform duration-300 group-hover:scale-105"
                  />
                </a>
              <p class="text-gray-300 mb-6 max-w-md">
                Your trusted partner for premium car rentals. Experience the freedom of the road with our diverse fleet and exceptional service.
              </p>
              <div class="flex space-x-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 border border-white/20">
                  <span class="text-2xl">🚙</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 border border-white/20">
                  <span class="text-2xl">🌟</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 border border-white/20">
                  <span class="text-2xl">🔒</span>
                </div>
              </div>
            </div>
            
            <!-- Quick Links -->
            <div>
              <h4 class="text-lg font-semibold mb-4 text-blue-300">Quick Links</h4>
              <ul class="space-y-3">
                <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200 inline-block">🏠 Home</a></li>
                <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200 inline-block">🚗 Browse Cars</a></li>
                <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200 inline-block">ℹ️ About Us</a></li>
                <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200 inline-block">📞 Contact</a></li>
              </ul>
            </div>
            
            <!-- Support -->
            <div>
              <h4 class="text-lg font-semibold mb-4 text-blue-300">Support</h4>
              <ul class="space-y-3">
                <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200 inline-block">❓ Help Center</a></li>
                <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200 inline-block">📋 Terms of Service</a></li>
                <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200 inline-block">🔐 Privacy Policy</a></li>
                <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200 inline-block">💬 Live Chat</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Bottom Section -->
      <div class="py-6">
        <div class="container mx-auto flex flex-col md:flex-row items-center justify-between px-4">
          <!-- Copyright -->
          <div class="text-gray-300 text-sm mb-4 md:mb-0">
            &copy; {{ new Date().getFullYear() }} Vheego. All rights reserved.
          </div>
          
          <!-- Social Links -->
          <div class="flex space-x-4">
            <a 
              href="#" 
              class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg p-3 hover:bg-blue-600 transition-all duration-300" 
              aria-label="Twitter"
            >
              <svg fill="currentColor" viewBox="0 0 24 24" class="h-5 w-5 text-gray-300 hover:text-white transition-colors duration-300">
                <path d="M22.46 6c-.77.35-1.6.58-2.47.69a4.3 4.3 0 0 0 1.88-2.37 8.59 8.59 0 0 1-2.72 1.04A4.28 4.28 0 0 0 16.11 4c-2.37 0-4.29 1.92-4.29 4.29 0 .34.04.67.1.99C7.69 8.99 4.07 7.13 1.64 4.15c-.37.64-.58 1.39-.58 2.19 0 1.51.77 2.84 1.95 3.62-.72-.02-1.4-.22-1.99-.55v.06c0 2.11 1.5 3.87 3.5 4.27-.36.1-.74.16-1.13.16-.28 0-.54-.03-.8-.08.54 1.7 2.12 2.94 3.99 2.97A8.6 8.6 0 0 1 2 19.54c-.29 0-.57-.02-.85-.05A12.13 12.13 0 0 0 8.29 21.5c7.55 0 11.68-6.26 11.68-11.68 0-.18-.01-.36-.02-.54A8.18 8.18 0 0 0 24 4.59a8.36 8.36 0 0 1-2.54.7z"/>
              </svg>
            </a>
            <a 
              href="#" 
              class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg p-3 hover:bg-blue-600 transition-all duration-300" 
              aria-label="Facebook"
            >
              <svg fill="currentColor" viewBox="0 0 24 24" class="h-5 w-5 text-gray-300 hover:text-white transition-colors duration-300">
                <path d="M22.675 0h-21.35C.595 0 0 .592 0 1.326v21.348C0 23.408.595 24 1.325 24h11.495v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.797.143v3.24l-1.918.001c-1.504 0-1.797.715-1.797 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116C23.406 24 24 23.408 24 22.674V1.326C24 .592 23.406 0 22.675 0"/>
              </svg>
            </a>
            <a 
              href="#" 
              class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg p-3 hover:bg-blue-600 transition-all duration-300" 
              aria-label="Instagram"
            >
              <svg fill="currentColor" viewBox="0 0 24 24" class="h-5 w-5 text-gray-300 hover:text-white transition-colors duration-300">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
              </svg>
            </a>
            <a 
              href="#" 
              class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg p-3 hover:bg-blue-600 transition-all duration-300" 
              aria-label="LinkedIn"
            >
              <svg fill="currentColor" viewBox="0 0 24 24" class="h-5 w-5 text-gray-300 hover:text-white transition-colors duration-300">
                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Decorative Elements -->
    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 via-cyan-500 to-blue-500"></div>
  </footer>
</template>

<script setup>
</script>

<style scoped>
/* Footer background pattern */
.footer-pattern {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M20 20c0 0 8-12 8-12s8 12 8 12-8 12-8 12-8-12-8-12zM8 8c0 0 4-6 4-6s4 6 4 6-4 6-4 6-4-6-4-6z'/%3E%3C/g%3E%3C/svg%3E");
}
</style>