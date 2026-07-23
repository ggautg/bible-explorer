<!-- resources/js/pages/Welcome.vue -->
<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { logout } from '@/routes'
const page = usePage()
import testimonials from '../helpers/testimonials.js'
import Testimonial from './components/testimonials.vue'

</script>

<template>

  <Head title="Constelaciones Bíblicas" />

  <div class="relative min-h-screen bg-bible-bg text-bible-text font-bible-body overflow-hidden">
    <!-- Campo de estrellas ambiental -->
    <div class="absolute inset-0 pointer-events-none opacity-60">
      <div class="stars"></div>
    </div>

    <main class="relative max-w-6xl mx-auto px-6 pt-8 pb-16 lg:pt-12 lg:pb-24
         grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-20
         items-start">
      <!-- Presentación principal: lado derecho -->
      <section class="w-full flex flex-col items-center text-center lg:sticky lg:top-24">
        <!-- Constelación ilustrativa -->
        <svg width="220" height="140" viewBox="0 0 220 140" class="mb-10" aria-hidden="true">
          <g stroke="#C9A227" stroke-opacity="0.5" stroke-width="1">
            <line x1="110" y1="70" x2="40" y2="30" />
            <line x1="110" y1="70" x2="180" y2="25" />
            <line x1="110" y1="70" x2="60" y2="115" />
            <line x1="110" y1="70" x2="165" y2="105" />
            <line x1="40" y1="30" x2="180" y2="25" />
            <line x1="60" y1="115" x2="165" y2="105" />
          </g>

          <circle cx="110" cy="70" r="6" fill="#E8C87A" />
          <circle cx="40" cy="30" r="3" fill="#8A7B54" />
          <circle cx="180" cy="25" r="3" fill="#8A7B54" />
          <circle cx="60" cy="115" r="3" fill="#8A7B54" />
          <circle cx="165" cy="105" r="3" fill="#8A7B54" />
        </svg>

        <h1 class="font-bible-display font-semibold text-4xl md:text-5xl
                 text-bible-accent-bright leading-tight mb-4">
          Cada versículo tiene su constelación
        </h1>

        <p class="text-bible-muted text-base md:text-lg max-w-xl
                 leading-relaxed mb-10">
          Explorá la Biblia no como texto lineal, sino como una red de
          conexiones. Cada versículo conecta con otros a través de citas,
          ecos y referencias — elegí un punto de partida y seguí el hilo.
        </p>

        <div class="flex flex-wrap gap-4 justify-center">
          <Link href="/leer"
            class="bg-bible-accent text-bible-bg font-medium px-6 py-3 rounded-md hover:bg-bible-accent-bright transition-colors">
            Empezar a leer
          </Link>
          <Link href="/bible/JHN/3/16"
            class="border border-bible-border text-bible-text px-6 py-3 rounded-md hover:border-bible-accent hover:text-bible-accent-bright transition-colors">
            Ver un ejemplo (Juan 3:16)
          </Link>
        </div>

        <div v-if="page.props.auth.user" class="mt-8 text-sm text-bible-muted">
          Ya iniciaste sesión como <span class="text-bible-accent-bright">{{ page.props.auth.user.name }}</span>
          ·
          <Link :href="logout()" method="post" as="button" class="hover:text-bible-error">
            Cerrar sesión
          </Link>
        </div>

        <div v-else class="flex gap-6 mt-8 text-sm">
          <Link href="/login" class="text-bible-muted hover:text-bible-accent-bright transition-colors">
            Iniciar sesión
          </Link>
          <Link href="/register" class="text-bible-muted hover:text-bible-accent-bright transition-colors">
            Crear cuenta
          </Link>
        </div>
      </section>
      <!-- Testimonios: lado izquierdo -->
      <section class="w-full">
        <h2 class="font-bible-display text-lg text-bible-muted uppercase
                 tracking-wide text-center lg:text-left mb-6">
          Lo que dicen quienes ya la usan
        </h2>

        <div class="grid grid-cols-1 gap-5">
          <Testimonial v-for="(testimonial, index) in testimonials" :key="index" :testimonial="testimonial" />
        </div>
      </section>

    </main>
  </div>
</template>

<style scoped>
.stars {
  width: 100%;
  height: 100%;
  background-image:
    radial-gradient(1.5px 1.5px at 20px 30px, #EDE6D6, transparent),
    radial-gradient(1.5px 1.5px at 120px 80px, #EDE6D6, transparent),
    radial-gradient(1px 1px at 200px 40px, #8F8874, transparent),
    radial-gradient(1.5px 1.5px at 300px 120px, #EDE6D6, transparent),
    radial-gradient(1px 1px at 400px 60px, #8F8874, transparent),
    radial-gradient(1.5px 1.5px at 500px 20px, #EDE6D6, transparent),
    radial-gradient(1px 1px at 600px 150px, #8F8874, transparent),
    radial-gradient(1.5px 1.5px at 700px 90px, #EDE6D6, transparent),
    radial-gradient(1px 1px at 80px 180px, #8F8874, transparent),
    radial-gradient(1.5px 1.5px at 350px 200px, #EDE6D6, transparent);
  background-repeat: repeat;
  background-size: 800px 300px;
}
</style>