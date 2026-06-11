"use client";

import { useState, useEffect } from "react";

const API_BASE = process.env.NEXT_PUBLIC_API_BASE ?? "http://127.0.0.1:8123/api";

type TranType = "CW" | "BE" | "MS";

type ApiResponse = {
  success: boolean;
  message: string;
  data: {
    transactionId: string;
    tranType: string;
    status: string;
    amount: number;
    rrn: string | null;
    providerTxnId: string | null;
    mobileNumber: string;
    aadhaarNumber: string;
    walletBalance: number;
  } | null;
  errors?: Record<string, string[]>;
};

const randomTxnId = () => `TXN-${Date.now()}`;

export default function Home() {
  const [transactionId, setTransactionId] = useState("");

  // Generate ID on client only to avoid SSR hydration mismatch
  useEffect(() => {
    setTransactionId(randomTxnId());
  }, []);
  const [tranType, setTranType] = useState<TranType>("CW");
  const [amount, setAmount] = useState("500");
  const [mobileNumber, setMobileNumber] = useState("9876543210");
  const [aadhaarNumber, setAadhaarNumber] = useState("123412341234");

  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState<ApiResponse | null>(null);
  const [errors, setErrors] = useState<Record<string, string[]>>({});

  const amountRequired = tranType === "CW";

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true);
    setResult(null);
    setErrors({});

    const payload: Record<string, unknown> = {
      transactionId,
      tranType,
      mobileNumber,
      aadhaarNumber,
    };
    if (amountRequired) payload.amount = Number(amount);

    try {
      const res = await fetch(`${API_BASE}/demo-aeps/transaction`, {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify(payload),
      });
      const json = (await res.json()) as ApiResponse;
      if (res.status === 422 && json.errors) setErrors(json.errors);
      setResult(json);
      // Rotate the txn id so the next submit isn't a duplicate.
      setTransactionId(randomTxnId());
    } catch {
      setResult({ success: false, message: "Network error — is the Laravel API running on :8123?", data: null });
    } finally {
      setLoading(false);
    }
  }

  const badge = result?.success
    ? "bg-green-100 text-green-800 border-green-300"
    : "bg-red-100 text-red-800 border-red-300";

  return (
    <main className="min-h-screen bg-zinc-50 dark:bg-zinc-950 flex items-center justify-center p-6">
      <div className="w-full max-w-xl">
        <h1 className="text-2xl font-semibold text-zinc-900 dark:text-zinc-50 mb-6">
          demo-provider-aeps
        </h1>


        <form onSubmit={submit} className="space-y-4 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
          <Field label="Transaction ID" error={errors.transactionId}>
            <input className={inputCls} value={transactionId} onChange={(e) => setTransactionId(e.target.value)} />
          </Field>

          <Field label="Transaction Type" error={errors.tranType}>
            <select className={inputCls} value={tranType} onChange={(e) => setTranType(e.target.value as TranType)}>
              <option value="CW">CW — Cash Withdrawal</option>
              <option value="BE">BE — Balance Enquiry</option>
              <option value="MS">MS — Mini Statement</option>
            </select>
          </Field>

          <Field label={`Amount${amountRequired ? " (required for CW)" : " (not required)"}`} error={errors.amount}>
            <input
              className={inputCls}
              type="number"
              value={amount}
              disabled={!amountRequired}
              onChange={(e) => setAmount(e.target.value)}
              placeholder={amountRequired ? "e.g. 500" : "—"}
            />
          </Field>

          <Field label="Mobile Number" error={errors.mobileNumber}>
            <input className={inputCls} value={mobileNumber} onChange={(e) => setMobileNumber(e.target.value)} maxLength={10} />
          </Field>

          <Field label="Aadhaar Number" error={errors.aadhaarNumber}>
            <input className={inputCls} value={aadhaarNumber} onChange={(e) => setAadhaarNumber(e.target.value)} maxLength={12} />
          </Field>

          <button
            type="submit"
            disabled={loading}
            className="w-full rounded-lg bg-zinc-900 dark:bg-zinc-50 text-white dark:text-zinc-900 font-medium py-2.5 disabled:opacity-50 transition"
          >
            {loading ? "Processing…" : "Submit transaction"}
          </button>
          <p className="text-xs text-zinc-400">Tip: a CW amount that is a multiple of 13 (e.g. 13, 26) is declined by the demo provider.</p>
        </form>

        {result && (
          <div className="mt-6 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
            <div className="flex items-center justify-between mb-3">
              <span className={`text-xs font-semibold px-2.5 py-1 rounded-full border ${badge}`}>
                {result.success ? "SUCCESS" : "FAILED"}
              </span>
              {result.data && (
                <span className="text-sm text-zinc-500">
                  AEPS wallet: <strong className="text-zinc-900 dark:text-zinc-50">₹{result.data.walletBalance}</strong>
                </span>
              )}
            </div>
            <p className="text-sm text-zinc-700 dark:text-zinc-300 mb-3">{result.message}</p>
            <pre className="text-xs bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg p-3 overflow-x-auto text-zinc-700 dark:text-zinc-300">
              {JSON.stringify(result.data ?? result.errors ?? {}, null, 2)}
            </pre>
          </div>
        )}
      </div>
    </main>
  );
}

const inputCls =
  "w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 outline-none focus:ring-2 focus:ring-zinc-400";

function Field({ label, error, children }: { label: string; error?: string[]; children: React.ReactNode }) {
  return (
    <label className="block">
      <span className="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">{label}</span>
      {children}
      {error?.length ? <span className="block text-xs text-red-600 mt-1">{error[0]}</span> : null}
    </label>
  );
}
