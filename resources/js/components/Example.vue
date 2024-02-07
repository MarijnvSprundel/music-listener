<script setup lang="ts">
    import { ref, onMounted } from "vue";
    import type {Ref} from "@vue/reactivity";
    import {StreamingService} from "../services/StreamingService";

    let audioWrapper: Ref = ref(null);

    let audioElement: null|HTMLAudioElement = null;

    onMounted(() => {
        initAudioElement();
    });

    function initAudioElement(): void {
        if (!audioWrapper.value) {
            return;
        }

        const innerElement: HTMLAudioElement = document.createElement('audio');

        innerElement.src = "/stream";

        audioWrapper.value.appendChild(innerElement);

        audioElement = innerElement;
    }

    function stream(): void {
        (new StreamingService)
            .stream()
            .then((response) => response.body)
            .then((body) => {
                if (!body) return;

                const reader = body.getReader();

                return new ReadableStream({
                    start(controller) {
                        return pump();

                        function pump(): any {
                            return reader.read().then(({ done, value }) => {
                                // When no more data needs to be consumed, close the stream
                                if (done) {
                                    controller.close();
                                    return;
                                }

                                // Enqueue the next data chunk into our target stream
                                controller.enqueue(value);
                                return pump();
                            });
                        }
                    },
                });
            })
            .then(stream => {
                // Create a new Response object with the custom ReadableStream
                const response = new Response(stream);

                // Convert the response to audio data using the Web Audio API
                return response.arrayBuffer();
            })
            .then(audioData => {
                // Play the audio using Web Audio API
                playAudio(audioData);
            })
            .catch(error => {
                console.error('Error fetching and playing audio:', error);
            });
    }

    function playAudio(audioData: ArrayBuffer) {
        // Create an AudioContext
        const audioContext = new (window.AudioContext)();
        console.log(audioData);
        // Decode the audio data
        audioContext.decodeAudioData(audioData, buffer => {
            const source = audioContext.createBufferSource();
            source.buffer = buffer;
            source.connect(audioContext.destination);
            source.start(0);
        });
    }
</script>

<template>
    <div>
        <h1 @click="audioElement && stream()">Example Component</h1>

        <div ref="audioWrapper"></div>
    </div>
</template>

<style lang="scss" scoped>

</style>
