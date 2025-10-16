export default function SidebarWidget() {
    return (
        <div className="px-4 py-6">
            <div className="rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 p-4 text-white">
                <h3 className="text-sm font-semibold mb-2">Admin Pro</h3>
                <p className="text-xs opacity-90 mb-3">
                    Upgrade to unlock advanced features and analytics.
                </p>
                <button className="w-full bg-white/20 hover:bg-white/30 text-white text-xs font-medium py-2 px-3 rounded-md transition-colors">
                    Upgrade Now
                </button>
            </div>
        </div>
    );
}