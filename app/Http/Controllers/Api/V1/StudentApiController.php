<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;

class StudentApiController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request, [
            'kelas' => ['nullable', 'string', 'max:100'],
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 50);

        $query = Siswa::query()
            ->select([
                'id',
                'nama',
                'nisn',
                'jenis_kelamin',
                'tanggal_lahir',
                'agama',
                'nama_ayah',
                'nama_ibu',
                'no_hp',
                'kelas',
                'alamat',
                'created_at',
                'updated_at',
            ]);

        $kelas = trim((string) ($validated['kelas'] ?? ''));
        if ($kelas !== '') {
            $query->where('kelas', $kelas);
        }

        $search = trim((string) ($validated['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('nisn', 'like', '%' . $search . '%')
                    ->orWhere('kelas', 'like', '%' . $search . '%');
            });
        }

        $students = $query
            ->orderBy('kelas')
            ->orderBy('nama')
            ->paginate($perPage, ['*'], 'page', $page);

        return $this->successResponse('Data siswa berhasil dimuat.', [
            'students' => $students
                ->getCollection()
                ->map(fn (Siswa $student) => $this->serializeStudent($student))
                ->values()
                ->all(),
            'meta' => $this->paginationMeta($students),
        ]);
    }

    public function detail(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request, [
            'id' => ['nullable', 'integer', 'min:1', 'required_without:nisn'],
            'nisn' => ['nullable', 'string', 'max:32', 'required_without:id'],
        ]);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $student = Siswa::query()
            ->when(isset($validated['id']), fn ($query) => $query->where('id', (int) $validated['id']))
            ->when(!isset($validated['id']), fn ($query) => $query->where('nisn', trim((string) ($validated['nisn'] ?? ''))))
            ->first();

        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan.', 404);
        }

        return $this->successResponse('Detail siswa berhasil dimuat.', [
            'student' => $this->serializeStudent($student),
        ]);
    }

    private function serializeStudent(Siswa $student): array
    {
        return [
            'id' => (int) $student->id,
            'nama' => (string) $student->nama,
            'nisn' => (string) $student->nisn,
            'jenis_kelamin' => $student->jenis_kelamin ? (string) $student->jenis_kelamin : null,
            'tanggal_lahir' => $student->tanggal_lahir?->format('Y-m-d'),
            'agama' => $student->agama ? (string) $student->agama : null,
            'nama_ayah' => $student->nama_ayah ? (string) $student->nama_ayah : null,
            'nama_ibu' => $student->nama_ibu ? (string) $student->nama_ibu : null,
            'no_hp' => $student->no_hp ? (string) $student->no_hp : null,
            'kelas' => $student->kelas ? (string) $student->kelas : null,
            'alamat' => $student->alamat ? (string) $student->alamat : null,
            'created_at' => $student->created_at?->toDateTimeString(),
            'updated_at' => $student->updated_at?->toDateTimeString(),
        ];
    }

    private function validatePayload(Request $request, array $rules): array|JsonResponse
    {
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', 422, [
                'errors' => $validator->errors(),
            ]);
        }

        return $validator->validated();
    }

    private function successResponse(string $message, array $data = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'rc' => 200,
            'data' => $data,
        ]);
    }

    private function errorResponse(string $message, int $statusCode = 422, array $data = []): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
            'rc' => $statusCode,
        ];

        if ($data !== []) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $statusCode);
    }

    private function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
