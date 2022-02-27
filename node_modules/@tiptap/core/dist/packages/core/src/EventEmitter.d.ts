export default class EventEmitter {
    private callbacks;
    on(event: string, fn: Function): this;
    protected emit(event: string, ...args: any): this;
    off(event: string, fn?: Function): this;
    protected removeAllListeners(): void;
}
