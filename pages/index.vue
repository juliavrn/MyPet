<template>
  <div class="fade-in">
    <div class="hero">
      <div class="hero-content">
        <h1 class="hero-title">
          Bienvenue sur <span class="hero-highlight">MyPet</span>
        </h1>
        <p class="hero-text">
          L'application complète pour gérer la santé et le bien-être de vos animaux de compagnie
        </p>
        <div class="hero-buttons">
          <NuxtLink to="/register" class="btn btn-primary">
            Commencer maintenant
          </NuxtLink>
          <NuxtLink to="/login" class="btn btn-secondary">
            Se connecter
          </NuxtLink>
        </div>
      </div>
    </div>

    <div class="features">
      <div class="feature-card">
        <div class="feature-icon">❤️</div>
        <h3 class="feature-title">Gestion des animaux</h3>
        <p class="feature-text">
          Créez et gérez les profils de vos animaux avec photos, informations et historique
        </p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">📋</div>
        <h3 class="feature-title">Suivi de santé</h3>
        <p class="feature-text">
          Gardez une trace des visites vétérinaires, vaccins et traitements
        </p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">📅</div>
        <h3 class="feature-title">Calendrier</h3>
        <p class="feature-text">
          Planifiez les rendez-vous et recevez des rappels pour les soins importants
        </p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">✅</div>
        <h3 class="feature-title">Listes de tâches</h3>
        <p class="feature-text">
          Créez des listes personnalisées pour les soins quotidiens et hebdomadaires
        </p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">📝</div>
        <h3 class="feature-title">Blog et conseils</h3>
        <p class="feature-text">
          Découvrez des articles et conseils pour prendre soin de vos animaux
        </p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">🏠</div>
        <h3 class="feature-title">Gestion complète</h3>
        <p class="feature-text">
          Une solution tout-en-un pour tous vos besoins de gestion d'animaux
        </p>
      </div>
    </div>

    <div class="cta">
      <h2 class="cta-title">Prêt à commencer ?</h2>
      <p class="cta-text">
        Rejoignez des milliers de propriétaires d'animaux qui font confiance à MyPet
      </p>
      <div class="cta-buttons">
        <NuxtLink to="/register" class="btn btn-primary btn-large">
          Créer un compte gratuit
        </NuxtLink>
        <NuxtLink to="/login" class="btn btn-secondary btn-large">
          Se connecter
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup>
// Page d'accueil simple et épurée
</script>

<style scoped>
.hero {
  background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
  border-radius: 16px;
  padding: 32px;
  margin-bottom: 48px;
  text-align: center;
}

.hero-content {
  max-width: 64rem;
  margin: 0 auto;
}

.hero-title {
  font-size: 3rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 24px;
}

.hero-highlight {
  color: #2563eb;
}

.hero-text {
  font-size: 1.25rem;
  color: #4b5563;
  margin-bottom: 32px;
  max-width: 42rem;
  margin-left: auto;
  margin-right: auto;
  padding: 0 16px;
}

.hero-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
  justify-content: center;
  padding: 0 16px;
}

@media (min-width: 640px) {
  .hero-buttons {
    flex-direction: row;
  }
}

.features {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
  margin-bottom: 48px;
}

@media (min-width: 640px) {
  .features {
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
  }
}

@media (min-width: 1024px) {
  .features {
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
  }
}

.feature-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.feature-icon {
  width: 48px;
  height: 48px;
  background: #f3f4f6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
  font-size: 1.5rem;
}

@media (min-width: 640px) {
  .feature-icon {
    width: 64px;
    height: 64px;
    font-size: 2rem;
  }
}

.feature-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 8px;
}

@media (min-width: 640px) {
  .feature-title {
    font-size: 1.25rem;
  }
}

.feature-text {
  font-size: 0.875rem;
  color: #4b5563;
  margin-bottom: 16px;
}

@media (min-width: 640px) {
  .feature-text {
    font-size: 1rem;
  }
}

.feature-link {
  display: inline-flex;
  align-items: center;
  margin-top: 12px;
  padding: 8px 16px;
  background: #059669;
  color: white;
  border-radius: 8px;
  font-size: 0.875rem;
  text-decoration: none;
  transition: background-color 0.2s;
}

.feature-link:hover {
  background: #047857;
}

.stats {
  margin-bottom: 48px;
}

.stats-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 24px;
  text-align: center;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

@media (min-width: 640px) {
  .stats-grid {
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
  }
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.stat-icon {
  font-size: 2rem;
  margin-bottom: 16px;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.stat-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 4px 0 0 0;
}

.cta {
  background: #2563eb;
  border-radius: 16px;
  padding: 32px;
  text-align: center;
  color: white;
}

.cta-title {
  font-size: 1.875rem;
  font-weight: 700;
  margin-bottom: 16px;
}

.cta-text {
  font-size: 1.125rem;
  margin-bottom: 24px;
  opacity: 0.9;
}

.cta-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
  justify-content: center;
}

@media (min-width: 640px) {
  .cta-buttons {
    flex-direction: row;
  }
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
}

.btn-primary {
  background: #2563eb;
  color: white;
}

.btn-primary:hover {
  background: #1d4ed8;
}

.btn-secondary {
  background: white;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover {
  background: #f9fafb;
}

.btn-large {
  padding: 16px 32px;
  font-size: 1.125rem;
}

.fade-in {
  animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style> 