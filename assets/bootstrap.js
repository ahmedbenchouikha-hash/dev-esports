import { startStimulusApp } from '@symfony/stimulus-bundle';
import PunitionVoiceController from './controllers/punition_voice_controller.js';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
app.register('punition-voice', PunitionVoiceController);
