@extends('layout.app')
@section('title','Bildirimler')
@section('content')
<div class="teacher-v2 teacher-v2-compact">
    <div class="teacher-v2-layout">
        <div class="teacher-v2-main">
            <section class="v2-hero card soft-surface soft-surface-blue">
                <div>
                    <h1>Bildirimler</h1>
                    <p>Web Push, tercih ve log yönetimi.</p>
                </div>
            </section>

            <section class="card soft-surface soft-surface-mint">
                <h2>Bildirim Gönder</h2>
                <form id="adminSendForm" class="parent-wa-form">
                    @csrf
                    <div class="parent-wa-row">
                        <label>Tip</label>
                        <select id="notifType" class="form-control" required>
                            @foreach($types as $type => $label)
                                <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="parent-wa-row">
                        <label>Hedef</label>
                        <select id="notifTarget" class="form-control" required>
                            @if($isAdmin)
                                <option value="all">Tüm Kullanıcılar</option>
                                <option value="self">Sadece Kendim</option>
                                <option value="admins">Sadece Adminler</option>
                                <option value="students">Sadece Öğrenciler</option>
                                <option value="teachers">Sadece Öğretmenler</option>
                                <option value="class">Sınıf Bazlı (Sınıfın Tamamı)</option>
                                <option value="class_student">Sınıf İçi Öğrenci Bazlı</option>
                                <option value="teacher">Öğretmen Bazlı (Tek Öğretmen)</option>
                            @else
                                <option value="self">Sadece Kendim</option>
                                <option value="students">Sadece Öğrenciler</option>
                                <option value="class">Sınıf Bazlı (Sınıfın Tamamı)</option>
                                <option value="class_student">Sınıf İçi Öğrenci Bazlı</option>
                            @endif
                        </select>
                    </div>
                    <div class="parent-wa-row" id="notifClassRow" style="display:none;">
                        <label>Sınıf</label>
                        <select id="notifClassId" class="form-control">
                            <option value="">Sınıf seçin</option>
                            @foreach($schoolClasses as $class)
                                <option value="{{ $class->id }}">{{ $class->name }} {{ $class->section ? ('-'.$class->section) : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="parent-wa-row" id="notifStudentRow" style="display:none;">
                        <label>Öğrenci</label>
                        <select id="notifStudentId" class="form-control">
                            <option value="">Öğrenci seçin</option>
                        </select>
                    </div>
                    <div class="parent-wa-row" id="notifTeacherRow" style="display:none;">
                        <label>Öğretmen</label>
                        <select id="notifTeacherId" class="form-control">
                            <option value="">Öğretmen seçin</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->user?->name ?? ('Öğretmen #'.$teacher->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="parent-wa-row"><label>Başlık</label><input id="notifTitle" class="form-control" maxlength="190" required></div>
                    <div class="parent-wa-row"><label>Mesaj</label><textarea id="notifBody" class="form-control" rows="4" maxlength="4000" required></textarea></div>
                    <div class="parent-wa-row"><label>Yönlendirme URL (opsiyonel)</label><input id="notifUrl" class="form-control" placeholder="{{ url('/dashboard') }}"></div>
                    <div class="parent-wa-actions"><button id="notifSendBtn" class="btn" type="submit">Gönder</button></div>
                </form>
                <div id="notifSendStatus" class="pdf-status">Hazır</div>
            </section>
        </div>
    </div>
</div>
@endsection
