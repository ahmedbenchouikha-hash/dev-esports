import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button', 'status', 'transcript', 'answer'];
    static values = {
        endpoint: String,
    };

    connect() {
        this.isListening = false;
        this.isSpeaking = false;
        this.isStarting = false;
        this.isProcessing = false;
        this.sessionActive = false;
        this.finalTranscriptBuffer = '';
        this.silenceTimer = null;
        this.recognition = null;

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (!SpeechRecognition) {
            this.updateStatus('Reconnaissance vocale non supportée (Chrome/Edge recommandé).');
            this.disableButton();
            return;
        }

        this.recognition = new SpeechRecognition();
        this.recognition.lang = 'fr-FR';
        this.recognition.interimResults = true;
        this.recognition.maxAlternatives = 1;
        this.recognition.continuous = true;

        this.recognition.onstart = () => {
            this.isStarting = false;
            this.isListening = true;
            this.renderButton();
            this.updateStatus('Écoute en cours...');
        };

        this.recognition.onend = () => {
            this.isStarting = false;
            this.isListening = false;
            this.renderButton();
            if (this.sessionActive && !this.isSpeaking && !this.isProcessing) {
                window.setTimeout(() => this.startListening(), 120);
                return;
            }

            if (!this.isSpeaking && !this.isProcessing) {
                this.updateStatus('Assistant prêt.');
            }
        };

        this.recognition.onerror = (event) => {
            this.isStarting = false;
            this.isListening = false;
            this.renderButton();
            const code = event?.error || 'inconnue';

            if (code === 'not-allowed' || code === 'service-not-allowed') {
                this.updateStatus('Micro refusé. Autorisez le micro dans le navigateur.');
                return;
            }

            if (code === 'audio-capture') {
                this.updateStatus('Aucun micro détecté. Vérifiez votre périphérique audio.');
                return;
            }

            if (code === 'network') {
                this.updateStatus('Erreur réseau de reconnaissance vocale.');
                return;
            }

            this.updateStatus('Erreur micro: ' + code);
        };

        this.recognition.onresult = async (event) => {
            let interimTranscript = '';

            for (let i = event.resultIndex; i < event.results.length; i++) {
                const result = event.results[i];
                const text = result?.[0]?.transcript?.trim() || '';
                if (!text) {
                    continue;
                }

                if (result.isFinal) {
                    this.finalTranscriptBuffer = `${this.finalTranscriptBuffer} ${text}`.trim();
                } else {
                    interimTranscript = `${interimTranscript} ${text}`.trim();
                }
            }

            const preview = `${this.finalTranscriptBuffer} ${interimTranscript}`.trim();
            if (preview !== '') {
                this.transcriptTarget.textContent = preview;
            }

            if (this.finalTranscriptBuffer !== '') {
                this.resetSilenceTimer();
            }
        };

        this.updateStatus('Assistant prêt.');
        this.renderButton();
    }

    disconnect() {
        this.stopSession();
        this.stopListening();
        this.clearSilenceTimer();
        window.speechSynthesis.cancel();
    }

    async toggle() {
        if (!this.recognition) {
            return;
        }

        if (this.isSpeaking) {
            window.speechSynthesis.cancel();
            this.isSpeaking = false;
        }

        if (this.sessionActive) {
            this.stopSession();
            return;
        }

        this.startSession();
    }

    startSession() {
        this.sessionActive = true;
        this.finalTranscriptBuffer = '';
        this.clearSilenceTimer();
        this.updateStatus('Session vocale active. Parlez librement...');
        this.renderButton();
        this.startListening();
    }

    stopSession() {
        this.sessionActive = false;
        this.clearSilenceTimer();
        this.stopListening();
        this.renderButton();
        this.updateStatus('Écoute arrêtée.');
    }

    startListening() {
        if (!this.recognition || this.isSpeaking || this.isListening || this.isStarting) {
            return;
        }

        const host = window.location.hostname;
        const isLocalhost = host === 'localhost' || host === '127.0.0.1';
        if (!window.isSecureContext && !isLocalhost) {
            this.updateStatus('Micro indisponible: utilisez HTTPS ou localhost.');
            return;
        }

        try {
            this.isStarting = true;
            this.recognition.start();
        } catch (error) {
            this.isStarting = false;

            if (error?.name === 'InvalidStateError') {
                this.updateStatus('Reconnaissance déjà active, réessayez dans 1 seconde.');
                return;
            }

            if (error?.name === 'NotAllowedError') {
                this.updateStatus('Micro refusé. Autorisez le micro puis réessayez.');
                return;
            }

            this.updateStatus('Impossible de démarrer l\'écoute (' + (error?.name || 'erreur') + ').');
        }
    }

    stopListening() {
        if (!this.recognition || !this.isListening) {
            return;
        }

        try {
            this.recognition.stop();
        } catch {
        }
    }

    async askAssistant(message) {
        this.updateStatus('Analyse IA...');
        this.isProcessing = true;

        try {
            const response = await fetch(this.endpointValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ message }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                this.answerTarget.textContent = data.error || 'Erreur assistant.';
                this.updateStatus('Erreur IA.');
                return;
            }

            const answer = (data.answer || '').trim();
            this.answerTarget.textContent = answer;

            if (answer !== '') {
                this.speak(answer);
            } else {
                this.updateStatus('Réponse vide.');
            }
        } catch {
            this.answerTarget.textContent = 'Erreur réseau vers le serveur.';
            this.updateStatus('Erreur réseau.');
        } finally {
            this.isProcessing = false;
            if (this.sessionActive && !this.isSpeaking) {
                this.startListening();
            }
        }
    }

    speak(text) {
        this.stopListening();
        this.isSpeaking = true;
        this.updateStatus('Assistant parle...');

        window.speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'fr-FR';
        utterance.rate = 1;
        utterance.pitch = 1;

        utterance.onend = () => {
            this.isSpeaking = false;
            if (this.sessionActive) {
                this.updateStatus('Session vocale active. Parlez...');
                this.startListening();
                return;
            }

            this.updateStatus('Assistant prêt.');
        };

        utterance.onerror = () => {
            this.isSpeaking = false;
            this.updateStatus('Erreur de synthèse vocale.');
        };

        window.speechSynthesis.speak(utterance);
    }

    disableButton() {
        if (this.hasButtonTarget) {
            this.buttonTarget.disabled = true;
        }
    }

    renderButton() {
        if (!this.hasButtonTarget) {
            return;
        }

        if (this.sessionActive) {
            this.buttonTarget.innerHTML = '<i class="fas fa-microphone-slash"></i> Stop';
            this.buttonTarget.classList.remove('btn-outline-primary');
            this.buttonTarget.classList.add('btn-danger');
            return;
        }

        this.buttonTarget.innerHTML = '<i class="fas fa-microphone"></i> Assistant Vocal';
        this.buttonTarget.classList.remove('btn-danger');
        this.buttonTarget.classList.add('btn-outline-primary');
    }

    updateStatus(text) {
        if (this.hasStatusTarget) {
            this.statusTarget.textContent = text;
        }
    }

    resetSilenceTimer() {
        this.clearSilenceTimer();
        this.silenceTimer = window.setTimeout(() => {
            this.submitBufferedTranscript();
        }, 1400);
    }

    clearSilenceTimer() {
        if (this.silenceTimer) {
            window.clearTimeout(this.silenceTimer);
            this.silenceTimer = null;
        }
    }

    async submitBufferedTranscript() {
        const message = this.finalTranscriptBuffer.trim();
        if (message === '' || this.isProcessing) {
            return;
        }

        this.finalTranscriptBuffer = '';
        this.clearSilenceTimer();
        this.stopListening();
        await this.askAssistant(message);
    }
}
