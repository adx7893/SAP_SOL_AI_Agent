public function register(): void
{
    $this->reportable(function (\Throwable $e) {
        \Log::error('🔥 GLOBAL ERROR', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);
    });
}
