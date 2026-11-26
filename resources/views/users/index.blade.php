<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Users') }}</h2>
    </x-slot>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Tier') }}</th>
                        <th>{{ __('Email verified') }}</th>
                        <th>{{ __('Registered') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->plan->label() }}</td>
                            <td>
                                @if ($user->email_verified_at)
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $users->links() }}
    </div>
</x-app-layout>
