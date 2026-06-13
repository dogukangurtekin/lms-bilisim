<div class="card mt-4">
  <div class="p-4 border-b border-slate-200 flex items-center justify-between">
    <div>
      <h3 class="text-lg font-bold">Günlük Çalışma</h3>
      <p class="text-sm text-slate-600">Bugünkü XP: {{ $todayXp ?? 0 }} • Seri: {{ $codingStreak?->current_streak ?? 0 }} gün</p>
    </div>
    <a href="{{ route('student.coding.index') }}" class="btn">Modüle Git</a>
  </div>
</div>
