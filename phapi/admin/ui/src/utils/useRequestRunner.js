import { useCallback, useRef } from "react";
import useSafeState from "./useSafeState";

/**
 * Promise handler hook, that handles results, errors and pending state of promises.
 * 
 * @param {() => Promise<Object>} defaultRequest default request function
 * @param {*} initialResult Initial value of result
 * 
 * @typedef RequestRunnerObj
 * @type {object}
 * 
 * @prop {boolean} isLoading 
 * True if a watched promise is pending
 * 
 * @prop {boolean} isSuccess
 * True if the watched promise is resolved succesfully
 * 
 * @prop {boolean} isDisplayable
 * True if not loading and has no error
 * 
 * @prop {string|null} error
 * Message of the last caught error. 
 * Null if there was no error.
 * 
 * @prop {Object} result
 * Result of the last watched promise.
 * 
 * @prop {(fn = null) => Promise<Object>} run 
 * Runs the given handler or the default handler if not provided.
 * Watches pending state, errors and result.
 * 
 * @prop {(fn = null) => Promise<Object>} runError
 * Runs the given handler or the default handler if not provided. 
 * Set isLoading member pased on promise status. 
 * If an error occures, stores the message and returns null.
 * Stores the result if the promise resolves successfully.
 * 
 * @prop {} runResult
 * Runs the given handler or the default handler if not provided.
 * Stores the result if the promise resolves successfully.
 * 
 * @prop {} runStatus
 * Runs the given handler or the default handler if not provided.
 * Set isLoading member pased on promise status. 
 * 
 * @prop {Function} setError
 * Manually update error.
 * 
 * @prop {Function} setResult
 * Manually update result.
  
 * 
 * @returns {RequestRunnerObj}
 */

export default function useRequestRunner(
  defaultRequest = null,
  initialResult = null
) {
  const [state, setState] = useSafeState({
    error: null,
    result: initialResult,
    isLoading: false,
    isSuccess: false,
  });

  const runError = useCallback(
    async (fn = null) => {
      try {
        if (typeof fn === "function") fn = await fn();
        if (fn instanceof Promise) fn = await fn;
        console.log("error", fn);
        return fn;
      } catch (ex) {
        setState((s) => ({ ...s, error: ex.message }));
        return null;
      }
    },
    [setState]
  );

  const runResult = useCallback(
    async (fn = null) => {
      try {
        let result = null;
        if (typeof fn === "function") result = await fn();
        else result = await fn;
        setState((s) => ({ ...s, result: result }));
        return result;
      } catch (ex) {
        throw ex;
      }
    },
    [setState]
  );

  const runningRef = useRef(null);
  const runStatus = useCallback(
    async (fn) => {
      console.log(fn);
      const promise = fn instanceof Promise ? fn : (async () => await fn())();
      runningRef.current = promise;
      try {
        setState((s) => ({ ...s, isLoading: true, isSuccess: false }));
        const result = await promise;
        setState((s) => ({ ...s, isLoading: false, isSuccess: true }));
        console.log("error", result);
        return result;
      } catch (ex) {
        if (runningRef.current === promise) runningRef.current = null;
        setState((s) => ({ ...s, isLoading: false, isSuccess: false }));
        throw ex;
      }
    },
    [setState, runningRef]
  );

  const run = useCallback(
    async (requestFn = null) => {
      if (requestFn == null) requestFn = defaultRequest;
      return runError(() => runStatus(() => runResult(requestFn)));
    },
    [defaultRequest, runResult, runError, runStatus]
  );

  return {
    isLoading: state.isLoading,
    result: state.result,
    error: state.error,
    isSuccess: state.isSuccess,
    isDisplayable: !state.isLoading && !state.error,
    run: run,
    runError: runError,
    runResult: runResult,
    runStatus: runStatus,
    setError: (error = null) =>
      setState((s) => ({
        ...s,
        error: error instanceof Error ? error.message : error,
      })),
    setResult: (result = null) => setState((s) => ({ ...s, result: result })),
  };
}
