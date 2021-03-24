import { useCallback, useRef } from "react";
import useSafeState from "./useSafeState";

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
        if (typeof fn === "function") return await fn();
        if (fn instanceof Promise) return await fn;
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
        setState((s) => ({ ...s, result: null }));
        throw ex;
      }
    },
    [setState]
  );

  const runningRef = useRef(null);
  const runStatus = useCallback(
    async (fn) => {
      const promise =
        typeof fn !== "function" ? fn : (async () => await fn())();
      runningRef.current = promise;
      try {
        setState((s) => ({ ...s, isLoading: true, isSuccess: false }));
        const result = await promise;
        setState((s) => ({ ...s, isLoading: false, isSuccess: true }));
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
    run: run,
    runError: runError,
    runResult: runResult,
    runStatus: runStatus,
    resetError: () => setState((s) => ({ ...s, error: null })),
  };
}
