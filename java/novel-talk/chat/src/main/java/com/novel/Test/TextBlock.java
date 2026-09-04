package com.novel.Test;

public class TextBlock {
	public static void main(String[] args) {
		String blockString = """
				SELECT this is
				FROM where
				AND TO
				""";

		String blockFormatter = """
				NAME IS %s
				AGE IS %d
				""".formatted("홍길동",5);
		System.out.println(blockString);
		System.out.println(blockFormatter);
	}
}
