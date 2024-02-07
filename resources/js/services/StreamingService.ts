export class StreamingService {
    public stream(): Promise<Response> {
        return fetch('/stream')
    }
}
