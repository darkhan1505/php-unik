
</main>

<footer class="border-t border-slate-200 bg-white mt-12">
  <div class="max-w-6xl mx-auto px-4 py-6 text-sm text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
    <span>&copy; <?= date('Y') ?> TaskFlow  учебный проект (PHP + PostgreSQL + Tailwind CSS)</span>
    <span>Сделано для демонстрации работы с базой данных</span>
  </div>
</footer>

<!-- Модальное окно подтверждения удаления -->
<div id="confirm-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50">
  <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6">
    <h3 class="text-lg font-semibold text-slate-800 mb-2">Подтвердите действие</h3>
    <p id="confirm-modal-text" class="text-sm text-slate-600 mb-6">Вы уверены?</p>
    <div class="flex justify-end gap-3">
      <button id="confirm-modal-cancel" type="button" class="px-4 py-2 rounded-md text-sm font-medium text-slate-600 hover:bg-slate-100">Отмена</button>
      <button id="confirm-modal-confirm" type="button" class="px-4 py-2 rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700">Удалить</button>
    </div>
  </div>
</div>

<script src="/assets/js/main.js"></script>
</body>
</html>
