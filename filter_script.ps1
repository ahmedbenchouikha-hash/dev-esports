if (Test-Path '.env') {
    $content = Get-Content '.env' -Raw
    $content = $content -replace 'BREVO_API_KEY="[^"]*"', 'BREVO_API_KEY=""'
    $content = $content -replace 'groqApiKey=.+$', 'groqApiKey='
    $content = $content -replace 'GEMINI_API_KEY=.+$', 'GEMINI_API_KEY='
    $content = $content -replace 'RAWG_API_KEY=.+$', 'RAWG_API_KEY='
    Set-Content '.env' $content -NoNewline
}
