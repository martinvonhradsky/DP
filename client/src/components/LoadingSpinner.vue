<template>
  <div class="flex flex-col items-center">
    <div class="spinner"></div>
    <h1>Loading...</h1>
  </div>
</template>

<style scoped>
.spinner {
  border-top: 2px solid #666;
  border-right: 2px solid #666;
  border-bottom: 2px solid #666;
  border-left: 2px solid transparent;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
