import React from "react";
import { Link, usePage } from "@inertiajs/react";

export default function AdminLayout({ children }) {
    const { url } = usePage();

    const navItems = [
        {
            label: "Dashboard",
            href: "/admin/dashboard",
            active: url.startsWith("/admin/dashboard"),
        },
        {
            label: "Products",
            href: "/admin/products",
            active: url.startsWith("/admin/products"),
        },
    ];

    return (
        <div className="min-h-screen bg-slate-50">
            {/* Sidebar */}
            <aside className="fixed top-0 left-0 h-full w-64 bg-slate-900 text-white z-50 flex flex-col">
                <div className="p-6 border-b border-white/10 shrink-0">
                    <Link href="/">
                        <h2 className="text-xl font-bold tracking-tight hover:opacity-80 transition cursor-pointer">
                            Power<span className="text-blue-400">Gen</span>
                        </h2>
                    </Link>
                </div>

                <nav className="p-4 space-y-1 flex-1 overflow-y-auto">
                    {navItems.map((item) => (
                        <Link
                            key={item.href}
                            href={item.href}
                            className={`flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-colors ${
                                item.active
                                    ? "bg-blue-600 text-white"
                                    : "text-slate-400 hover:bg-white/5 hover:text-white"
                            }`}
                        >
                            {/* You can add icons here based on label */}
                            {item.label}
                        </Link>
                    ))}
                </nav>

                <div className="p-4 border-t border-white/10 shrink-0">
                    <Link
                        href="/admin/logout"
                        method="post"
                        as="button"
                        className="flex w-full items-center justify-center gap-2 px-4 py-2 rounded-lg bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition-colors text-sm font-medium"
                    >
                        Sign Out
                    </Link>
                </div>
            </aside>

            {/* Main Content */}
            <main className="pl-64">
                <header className="h-16 bg-white border-b border-slate-200 sticky top-0 z-40 px-8 flex items-center justify-between">
                    <h1 className="text-lg font-semibold text-slate-700">
                        {/* Dynamic Title based on current page could go here */}
                        Overview
                    </h1>
                    <div className="flex items-center gap-4">
                        <div className="h-6 w-px bg-slate-200"></div>
                        <span className="text-sm font-medium text-slate-600">
                            {usePage().props.auth.user.name}
                        </span>
                        <div className="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 font-bold">
                            {usePage().props.auth.user.name.charAt(0)}
                        </div>
                    </div>
                </header>

                <div className="p-8">{children}</div>
            </main>
        </div>
    );
}
