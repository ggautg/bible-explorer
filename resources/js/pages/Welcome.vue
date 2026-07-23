<!-- resources/js/pages/Welcome.vue -->
<script setup>
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>

  <Head title="Constelaciones Bíblicas" />

  <div class="relative min-h-screen bg-bible-bg text-bible-text font-bible-body overflow-hidden">
    <!-- Campo de estrellas ambiental -->
    <div class="absolute inset-0 pointer-events-none opacity-60">
      <div class="stars"></div>
    </div>

    <div class="relative max-w-3xl mx-auto px-6 pt-24 pb-16 flex flex-col items-center text-center">
      <!-- Constelación ilustrativa -->
      <svg width="220" height="140" viewBox="0 0 220 140" class="mb-10">
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

      <h1 class="font-bible-display font-semibold text-4xl md:text-5xl text-bible-accent-bright leading-tight mb-4">
        Cada versículo tiene su constelación
      </h1>

      <p class="text-bible-muted text-base md:text-lg max-w-xl leading-relaxed mb-10">
        Explorá la Biblia no como texto lineal, sino como una red de conexiones.
        Cada versículo conecta con otros a través de citas, ecos y referencias —
        elegí un punto de partida y seguí el hilo.
      </p>

      <div class="flex flex-wrap gap-4 justify-center">
        <Link href="/bible/GEN/1/1"
          class="bg-bible-accent text-bible-bg font-medium px-6 py-3 rounded-md hover:bg-bible-accent-bright transition-colors">
          Empezar a explorar
        </Link>
        <Link href="/bible/JHN/3/16"
          class="border border-bible-border text-bible-text px-6 py-3 rounded-md hover:border-bible-accent hover:text-bible-accent-bright transition-colors">
          Ver un ejemplo (Juan 3:16)
        </Link>
      </div>

      <div class="flex gap-6 mt-8 text-sm">
        <Link href="/login" class="text-bible-muted hover:text-bible-accent-bright transition-colors">
          Iniciar sesión
        </Link>
        <Link href="/register" class="text-bible-muted hover:text-bible-accent-bright transition-colors">
          Crear cuenta
        </Link>
      </div>

      <section class="mt-20 mb-8 max-w-3xl mx-auto">
        <h2 class="font-bible-display text-lg text-bible-muted uppercase tracking-wide text-center mb-6">
          Lo que dicen quienes ya la usan
        </h2>
        <div class="grid sm:grid-cols-2 gap-5">
          <div class="bg-bible-panel border border-bible-border rounded-lg p-6">
            <svg width="28" height="20" viewBox="0 0 28 20" class="text-bible-accent mb-3 opacity-60">
              <path fill="currentColor"
                d="M0 20V11.4C0 7.73 0.933 4.867 2.8 2.8C4.667 0.933 7.133 0 10.2 0V4.2C8.333 4.2 6.9 4.733 5.9 5.8C4.9 6.733 4.4 8.067 4.4 9.8H10.2V20H0ZM17.8 20V11.4C17.8 7.73 18.733 4.867 20.6 2.8C22.467 0.933 24.933 0 28 0V4.2C26.133 4.2 24.7 4.733 23.7 5.8C22.7 6.733 22.2 8.067 22.2 9.8H28V20H17.8Z" />
            </svg>
            <p class="font-bible-display text-lg text-bible-text leading-relaxed mb-2">
              La app me convenció de que Dios es real, y me ayudó a entender la Biblia de una manera que nunca antes había experimentado para mi y para mis alumnos.
            </p>
            <p class="text-sm text-bible-muted mb-4">
              Pastor y profesor de Biblia, usa la app para preparar sus clases
            </p>
            <div class="flex items-center gap-3">
              <img src="/images/testimonials/giovanni-arestivo.png" alt="Giovanni Arestivo"
                class="w-10 h-10 rounded-full object-cover border border-bible-border" />
              <span class="text-sm text-bible-muted">Giovanni Arestivo</span>
            </div>
          </div>

          <div class="bg-bible-panel border border-bible-border rounded-lg p-6">
            <svg width="28" height="20" viewBox="0 0 28 20" class="text-bible-accent mb-3 opacity-60">
              <path fill="currentColor"
                d="M0 20V11.4C0 7.73 0.933 4.867 2.8 2.8C4.667 0.933 7.133 0 10.2 0V4.2C8.333 4.2 6.9 4.733 5.9 5.8C4.9 6.733 4.4 8.067 4.4 9.8H10.2V20H0ZM17.8 20V11.4C17.8 7.73 18.733 4.867 20.6 2.8C22.467 0.933 24.933 0 28 0V4.2C26.133 4.2 24.7 4.733 23.7 5.8C22.7 6.733 22.2 8.067 22.2 9.8H28V20H17.8Z" />
            </svg>
            <p class="font-bible-display text-lg text-bible-text leading-relaxed mb-2">
              La app me motivó a seguir en Cristo y a estudiar la Biblia más a fondo, y me ayuda a mantenerme enfocado en lo que es importante.
            </p>
            <p class="text-sm text-bible-muted mb-4">
              La usa desde hace 2 años y la recomienda a sus amigos y familiares
            </p>
            <div class="flex items-center gap-3">
              <img src="/images/testimonials/piero-arestivo.png" alt="Piero Arestivo"
                class="w-10 h-10 rounded-full object-cover border border-bible-border" />
              <span class="text-sm text-bible-muted">Piero Arestivo</span>
            </div>
          </div>

          <div class="bg-bible-panel border border-bible-border rounded-lg p-6">
            <svg width="28" height="20" viewBox="0 0 28 20" class="text-bible-accent mb-3 opacity-60">
              <path fill="currentColor"
                d="M0 20V11.4C0 7.73 0.933 4.867 2.8 2.8C4.667 0.933 7.133 0 10.2 0V4.2C8.333 4.2 6.9 4.733 5.9 5.8C4.9 6.733 4.4 8.067 4.4 9.8H10.2V20H0ZM17.8 20V11.4C17.8 7.73 18.733 4.867 20.6 2.8C22.467 0.933 24.933 0 28 0V4.2C26.133 4.2 24.7 4.733 23.7 5.8C22.7 6.733 22.2 8.067 22.2 9.8H28V20H17.8Z" />
            </svg>
            <p class="font-bible-display text-lg text-bible-text leading-relaxed mb-2">
              Yo era ateo, me bajé la app con total escepticismo con la intención de refutarla, pero terminé convencido
              de que Dios es real
            </p>
            <p class="text-sm text-bible-muted mb-4">
              Ex-ateo
            </p>
            <div class="flex items-center gap-3">
              <img src="/images/testimonials/jose-britez.jpg" alt="José Brítez"
                class="w-10 h-10 rounded-full object-cover border border-bible-border" />
              <span class="text-sm text-bible-muted">José Brítez</span>
            </div>
          </div>
        </div>
      </section>
    </div>
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