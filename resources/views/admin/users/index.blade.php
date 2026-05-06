<x-app-layout>
  <div class="page" style="max-width:860px;">
    <div class="page-header">
      <div>
        <div class="page-title">Benutzerverwaltung</div>
        <div class="page-subtitle">Rollen und Berechtigungen verwalten</div>
      </div>
    </div>

    @if(session('success'))
      <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:13px;">
        {{ session('success') }}
      </div>
    @endif

    @if($errors->any())
      <div style="background:#fef2f2; border:1px solid #fecaca; color:#dc2626; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:13px;">
        {{ $errors->first() }}
      </div>
    @endif

    <div class="card" style="padding:0; overflow:hidden;">
      <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead>
          <tr style="border-bottom:1px solid var(--border); background:var(--bg-2);">
            <th style="text-align:left; padding:12px 16px; font-weight:600; color:var(--text-2);">Name</th>
            <th style="text-align:left; padding:12px 16px; font-weight:600; color:var(--text-2);">E-Mail</th>
            <th style="text-align:center; padding:12px 16px; font-weight:600; color:var(--text-2);">Leads</th>
            <th style="text-align:left; padding:12px 16px; font-weight:600; color:var(--text-2);">Rolle</th>
            <th style="padding:12px 16px;"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $user)
            <tr style="border-bottom:1px solid var(--border); {{ $user->id === auth()->id() ? 'background:var(--bg-2);' : '' }}">
              <td style="padding:12px 16px;">
                {{ $user->name }}
                @if($user->id === auth()->id())
                  <span style="font-size:11px; color:var(--text-3); margin-left:4px;">(Du)</span>
                @endif
              </td>
              <td style="padding:12px 16px; color:var(--text-2);">{{ $user->email }}</td>
              <td style="padding:12px 16px; text-align:center; color:var(--text-2);">{{ $user->leads_count }}</td>
              <td style="padding:12px 16px;">
                @if($user->id === auth()->id())
                  <span style="
                    display:inline-block; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:500;
                    {{ $user->role === 'admin' ? 'background:#ede9fe; color:#7c3aed;' : 'background:#f1f5f9; color:#475569;' }}
                  ">{{ ucfirst($user->role) }}</span>
                @else
                  <form method="POST" action="/admin/users/{{ $user->id }}" style="display:inline;">
                    @csrf
                    @method('PATCH')
                    <select
                      name="role"
                      onchange="this.form.submit()"
                      style="font-size:12px; padding:4px 8px; border:1px solid var(--border); border-radius:6px; background:var(--bg); cursor:pointer;"
                    >
                      <option value="agent"  {{ $user->role === 'agent'  ? 'selected' : '' }}>Agent</option>
                      <option value="admin"  {{ $user->role === 'admin'  ? 'selected' : '' }}>Admin</option>
                    </select>
                  </form>
                @endif
              </td>
              <td style="padding:12px 16px; text-align:right; color:var(--text-3); font-size:12px;">
                {{ $user->created_at->format('d.m.Y') }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</x-app-layout>
