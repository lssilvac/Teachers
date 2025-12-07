<div>

    @if ($invites->isEmpty())
        <p style="font-size: 0.875rem; color: #9ca3af;">
            Não há convites para este módulo ainda.
        </p>
    @else
        <div style="overflow-x: auto; margin-top: 0.5rem;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                <tr>
                    <th style="text-align: left; padding: 6px 12px; border: 1px solid #6b7280;">
                        Professor
                    </th>
                    <th style="text-align: left; padding: 6px 12px; border: 1px solid #6b7280;">
                        Disciplina
                    </th>
                    <th style="text-align: left; padding: 6px 12px; border: 1px solid #6b7280;">
                        Status
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach ($invites as $invite)
                    <tr>
                        {{-- Professor --}}
                        <td style="padding: 6px 12px; border: 1px solid #374151; white-space: nowrap;">
                            {{ $invite->teacher?->name ?? 'Sem professor' }}
                        </td>

                        {{-- Disciplina --}}
                        <td style="padding: 6px 12px; border: 1px solid #374151;">
                            {{ $invite->subject?->name ?? 'Sem disciplina' }}
                        </td>

                        {{-- Status como "badge" simples --}}
                        <td style="padding: 6px 12px; border: 1px solid #374151;">
                            @php
                                $status = strtolower($invite->status ?? 'pending');

                                $label = match ($status) {
                                    'pending'  => 'Pendente',
                                    'accepted' => 'Aceito',
                                    'rejected' => 'Recusado',
                                    default    => ucfirst($status),
                                };

                                $bgColor = match ($status) {
                                    'pending'  => '#f59e0b33', // amarelo suave
                                    'accepted' => '#22c55e33', // verde suave
                                    'rejected' => '#ef444433', // vermelho suave
                                    default    => '#6b728033', // cinza
                                };

                                $textColor = '#e5e7eb';
                            @endphp

                            <span style="
                                    display: inline-block;
                                    padding: 3px 10px;
                                    border-radius: 9999px;
                                    background-color: {{ $bgColor }};
                                    color: {{ $textColor }};
                                ">
                                    {{ $label }}
                                </span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>
